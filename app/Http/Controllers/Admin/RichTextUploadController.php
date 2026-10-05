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
    private const FILE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/zip' => 'zip',
        'text/plain' => 'txt',
    ];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,text/plain'],
            'type' => ['nullable', Rule::in(['image', 'file'])],
        ]);

        $file = $validated['file'];
        $mime = (string) $file->getMimeType();
        $type = $validated['type'] ?? (str_starts_with($mime, 'image/') ? 'image' : 'file');
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

        $extension = self::FILE_MIME_EXTENSIONS[$mime] ?? null;
        if (! $extension) {
            return response()->json(['message' => 'نوع واقعی فایل برای بارگذاری پشتیبانی نمی‌شود.'], 422);
        }

        $contents = file_get_contents($file->getRealPath());
        if (! is_string($contents) || $contents === '') {
            return response()->json(['message' => 'خواندن فایل بارگذاری‌شده ناموفق بود.'], 422);
        }

        $name = Str::uuid().'.'.$extension;
        $path = $directory.'/'.$name;
        $storage = Storage::disk('public');

        if (! $storage->put($path, $contents)) {
            return response()->json(['message' => 'ذخیره فایل روی سرور ناموفق بود؛ لطفاً دوباره تلاش کنید.'], 422);
        }

        return response()->json([
            'location' => $storage->url($path),
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
