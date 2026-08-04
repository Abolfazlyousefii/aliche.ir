<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MediaLibraryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RichTextUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,text/plain'],
            'type' => ['nullable', Rule::in(['image', 'file'])],
        ]);

        $file = $validated['file'];
        $type = $validated['type'] ?? (str_starts_with((string) $file->getMimeType(), 'image/') ? 'image' : 'file');
        $directory = $type === 'image' ? 'rich-text/images' : 'rich-text/files';
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $name = Str::uuid().'.'.$extension;
        $path = $directory.'/'.$name;
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        if ($type === 'image') {
            app(MediaLibraryService::class)->recordStoredImage($file, $path, 'public', $request->user()?->id);
        }

        return response()->json([
            'location' => Storage::disk('public')->url($path),
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
