<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaLibraryService
{
    public function storeImage(UploadedFile $file, string $directory, string $disk = 'public', ?int $uploadedBy = null, ?string $title = null, ?string $altText = null): Media
    {
        $path = $file->store($directory, $disk);

        return $this->recordStoredImage($file, $path, $disk, $uploadedBy, $title, $altText);
    }

    public function recordStoredImage(UploadedFile $file, string $path, string $disk = 'public', ?int $uploadedBy = null, ?string $title = null, ?string $altText = null): Media
    {
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        $this->mirrorPublicDiskFile($path, $disk);

        return Media::query()->firstOrCreate(
            ['path' => $path],
            [
                'file_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'mime_type' => $file->getMimeType(),
                'extension' => $file->extension() ?: $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'alt_text' => $altText,
                'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'uploaded_by' => $uploadedBy,
                'hash' => is_file($file->getRealPath()) ? hash_file('sha256', $file->getRealPath()) : null,
            ]
        );
    }

    public function publicUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    private function mirrorPublicDiskFile(string $path, string $disk = 'public'): void
    {
        if ($disk !== 'public') {
            return;
        }

        $source = Storage::disk('public')->path($path);

        if (! is_file($source)) {
            return;
        }

        foreach ($this->publicMirrorTargets($path) as $target) {
            if ($this->isSameFileLocation($source, $target)) {
                continue;
            }

            try {
                if (! is_dir(dirname($target))) {
                    mkdir(dirname($target), 0755, true);
                }

                copy($source, $target);
            } catch (Throwable) {
                // The canonical file is already saved on the public disk; ignore
                // mirror failures so uploads are not blocked by server layout.
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function publicMirrorTargets(string $path): array
    {
        return [
            public_path('storage/'.$path),
            public_path('media/'.$path),
            public_path('media-files/'.$path),
            public_path('uploaded-media/'.$path),
        ];
    }

    private function isSameFileLocation(string $source, string $target): bool
    {
        $sourceRealPath = realpath($source);
        $targetDirectoryRealPath = realpath(dirname($target));

        if ($sourceRealPath === false || $targetDirectoryRealPath === false) {
            return false;
        }

        return $sourceRealPath === $targetDirectoryRealPath.DIRECTORY_SEPARATOR.basename($target);
    }
}
