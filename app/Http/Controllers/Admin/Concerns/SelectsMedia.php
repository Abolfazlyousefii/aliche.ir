<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

trait SelectsMedia
{
    protected function selectedMediaPath(Request $request, string $field): ?string
    {
        $id = $request->input("{$field}_media_id");

        if (blank($id)) {
            return null;
        }

        return Media::query()
            ->whereKey($id)
            ->images()
            ->value('path');
    }

    protected function uploadedOrSelectedImage(Request $request, string $field, string $directory, string $disk = 'public'): ?string
    {
        if ($request->hasFile($field)) {
            $storage = Storage::disk($disk);
            $storage->makeDirectory($directory);

            $path = app(MediaLibraryService::class)->storeImage(
                $request->file($field),
                $directory,
                $disk,
                $request->user()?->id
            )->path;

            if (! is_string($path) || $path === '' || ! $storage->exists($path)) {
                throw new RuntimeException('Uploaded image could not be saved.');
            }

            $this->mirrorPublicDiskFile($path, $disk);

            return $path;
        }

        return $this->selectedMediaPath($request, $field);
    }

    protected function mirrorPublicDiskFile(string $path, string $disk = 'public'): void
    {
        if ($disk !== 'public') {
            return;
        }

        $source = Storage::disk('public')->path($path);
        $targets = [
            public_path('storage/'.$path),
            public_path('media-files/'.$path),
            public_path('uploaded-media/'.$path),
        ];

        foreach ($targets as $target) {
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
