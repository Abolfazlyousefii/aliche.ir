<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaLibraryService
{
    public function storeImage(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        ?int $uploadedBy = null,
        ?string $title = null,
        ?string $altText = null
    ): Media {
        $path = $this->optimizeAndStore($file, $directory, $disk);

        [$width, $height] = $this->imageSize($path, $disk);

        return Media::query()->firstOrCreate(
            ['path' => $path],
            [
                'file_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size' => Storage::disk($disk)->size($path),
                'width' => $width,
                'height' => $height,
                'alt_text' => $altText,
                'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'uploaded_by' => $uploadedBy,
                'hash' => hash_file('sha256', $file->getRealPath()),
            ]
        );
    }

    private function optimizeAndStore(UploadedFile $file, string $directory, string $disk): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $path = $directory.'/'.uniqid($name.'-').'.webp';

        $imageInfo = getimagesize($file->getRealPath());

        if (!$imageInfo) {
            throw new \RuntimeException('فایل تصویر معتبر نیست.');
        }

        [$width, $height] = $imageInfo;

        $source = match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => null,
        };

        if (!$source) {
            throw new \RuntimeException('فرمت تصویر پشتیبانی نمی‌شود.');
        }

        $maxWidth = 1600;

        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * $ratio);

            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);

            imagecopyresampled(
                $canvas,
                $source,
                0,0,0,0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            imagedestroy($source);
            $source = $canvas;
        }

        ob_start();
        imagewebp($source, null, 82);
        $content = ob_get_clean();

        imagedestroy($source);

        Storage::disk($disk)->put($path, $content);

        return $path;
    }

    private function imageSize(string $path, string $disk): array
    {
        $temp = Storage::disk($disk)->path($path);
        return @getimagesize($temp) ?: [null, null];
    }
}
