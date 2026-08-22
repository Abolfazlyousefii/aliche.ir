<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Rules\SafeImageUpload;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

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
        $directory = $type === 'image' ? 'rich-text/images/'.now()->format('Y/m') : 'rich-text/files/'.now()->format('Y/m');

        if ($type === 'image') {
            $request->validate([
                'file' => ['bail', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            ]);
            try {
                $media = app(MediaLibraryService::class)->storeImage($file, $directory, 'public', $request->user()?->id);
            } catch (Throwable $exception) {
                report($exception);

                return response()->json(['message' => 'پردازش تصویر انجام نشد؛ لطفاً فایل سالم دیگری انتخاب کنید.'], 422);
            }

            return response()->json([
                'location' => $media->url,
                'path' => $media->path,
                'name' => $media->original_name,
            ]);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $name = Str::uuid().'.'.$extension;
        $path = $directory.'/'.$name;
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return response()->json([
            'location' => Storage::disk('public')->url($path),
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
