<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

            try {
                $path = app(MediaLibraryService::class)->storeImage(
                    $request->file($field),
                    $directory,
                    $disk,
                    $request->user()?->id
                )->path;
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages([$field => 'پردازش تصویر انجام نشد؛ لطفاً یک تصویر سالم دیگر انتخاب کنید.']);
            }

            if (! is_string($path) || $path === '' || ! $storage->exists($path)) {
                throw new RuntimeException('Uploaded image could not be saved.');
            }

            return $path;
        }

        return $this->selectedMediaPath($request, $field);
    }
}
