<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MediaLibraryService
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    public function storeImage(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        ?int $uploadedBy = null,
        ?string $title = null,
        ?string $altText = null
    ): Media {
        $this->assertValidImage($file);
        $storedPaths = [];

        try {
            $result = $this->processAndStore($file, $directory, $disk);
            $storedPaths = $result['paths'];

            return DB::transaction(fn () => Media::query()->create([
                'file_name' => basename($result['path']),
                'original_name' => $file->getClientOriginalName(),
                'path' => $result['path'],
                'disk' => $disk,
                'mime_type' => $result['mime'],
                'extension' => $result['extension'],
                'size' => Storage::disk($disk)->size($result['path']),
                'width' => $result['width'],
                'height' => $result['height'],
                'alt_text' => $altText,
                'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'uploaded_by' => $uploadedBy,
                'hash' => hash_file('sha256', Storage::disk($disk)->path($result['path'])),
            ]));
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk($disk)->delete($path);
            }

            Log::error('Image upload or optimization failed.', [
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('پردازش تصویر انجام نشد. لطفاً یک تصویر سالم دیگر انتخاب کنید.', previous: $exception);
        }
    }

    public function deleteFiles(Media $media): void
    {
        if ($media->isExternalOrAsset()) {
            return;
        }

        Storage::disk($media->disk ?: 'public')->delete($this->pathsFor($media->path));
    }

    /** @return array<int, string> */
    public function pathsFor(string $path): array
    {
        $paths = [$path];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $base = substr($path, 0, -strlen($extension) - 1);

        foreach ((array) config('media.variant_widths', [400, 800]) as $width) {
            $paths[] = $base.'-'.$width.'w.'.$extension;
        }

        return $paths;
    }

    private function assertValidImage(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new RuntimeException('فایل تصویر به‌درستی دریافت نشد.');
        }

        if (($file->getSize() ?: 0) > ((int) config('media.max_upload_kilobytes', 5120) * 1024)) {
            throw new RuntimeException('حجم تصویر نباید بیشتر از ۵ مگابایت باشد.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';
        $extension = strtolower($file->getClientOriginalExtension());
        $info = @getimagesize($file->getRealPath());
        $expected = self::MIME_EXTENSIONS[$mime] ?? null;

        if ($info === false || $expected === null || ($expected === 'jpg' ? ! in_array($extension, ['jpg', 'jpeg'], true) : $extension !== $expected)) {
            throw new RuntimeException('پسوند و نوع واقعی فایل تصویر معتبر یا هماهنگ نیست.');
        }

        $maxPixels = max(1, (int) config('media.max_pixels', 40000000));
        if ((int) $info[1] > 0 && (int) $info[0] > intdiv($maxPixels, (int) $info[1])) {
            throw new RuntimeException('ابعاد تصویر بیش از حد بزرگ است.');
        }

        if ($mime === 'image/avif' && (! function_exists('imagecreatefromavif') || ! function_exists('imageavif'))) {
            throw new RuntimeException('پردازش AVIF در این سرور پشتیبانی نمی‌شود.');
        }
    }

    /** @return array{path:string,paths:array<int,string>,mime:string,extension:string,width:int,height:int} */
    private function processAndStore(UploadedFile $file, string $directory, string $disk): array
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory === '' || str_contains($directory, '..')) {
            throw new RuntimeException('مسیر ذخیره‌سازی تصویر معتبر نیست.');
        }
        if (! preg_match('#/20\d{2}/(?:0[1-9]|1[0-2])$#', '/'.$directory)) {
            $directory .= '/'.now()->format('Y/m');
        }

        $info = getimagesize($file->getRealPath());
        $mime = (string) $info['mime'];
        [$originalWidth, $originalHeight] = $info;

        // GD flattens animated GIFs; retaining the original is the safe optimization fallback.
        if ($mime === 'image/gif') {
            $path = $directory.'/'.Str::uuid().'.gif';
            $this->putOrFail($disk, $path, file_get_contents($file->getRealPath()));

            return ['path' => $path, 'paths' => [$path], 'mime' => $mime, 'extension' => 'gif', 'width' => $originalWidth, 'height' => $originalHeight];
        }

        // A server able to identify but not encode WebP must still accept an existing valid WebP.
        if ($mime === 'image/webp' && ! function_exists('imagewebp')) {
            $path = $directory.'/'.Str::uuid().'.webp';
            $this->putOrFail($disk, $path, file_get_contents($file->getRealPath()));

            return ['path' => $path, 'paths' => [$path], 'mime' => $mime, 'extension' => 'webp', 'width' => $originalWidth, 'height' => $originalHeight];
        }

        $source = $this->createImageResource($file->getRealPath(), $mime);
        $source = $this->applyExifOrientation($source, $file->getRealPath(), $mime);
        $width = imagesx($source);
        $height = imagesy($source);
        $maxWidth = max(1, (int) config('media.max_width', 1920));
        $targetWidth = min($width, $maxWidth);
        $targetHeight = (int) max(1, round($height * ($targetWidth / $width)));
        $outputMime = function_exists('imagewebp') ? 'image/webp' : $mime;
        $extension = self::MIME_EXTENSIONS[$outputMime];
        $base = $directory.'/'.Str::uuid();
        $paths = [];

        try {
            $main = $this->resize($source, $width, $height, $targetWidth, $targetHeight);
            $path = $base.'.'.$extension;
            $mainContent = $mime === 'image/webp' && $targetWidth === $width
                ? file_get_contents($file->getRealPath())
                : $this->encode($main, $outputMime);
            $this->putOrFail($disk, $path, $mainContent);
            $paths[] = $path;
            imagedestroy($main);

            foreach ((array) config('media.variant_widths', [400, 800]) as $variantWidth) {
                $variantWidth = (int) $variantWidth;
                if ($variantWidth < 1 || $variantWidth >= $targetWidth) {
                    continue;
                }

                $variantHeight = (int) max(1, round($height * ($variantWidth / $width)));
                $variant = $this->resize($source, $width, $height, $variantWidth, $variantHeight);
                $variantPath = $base.'-'.$variantWidth.'w.'.$extension;
                $this->putOrFail($disk, $variantPath, $this->encode($variant, $outputMime));
                $paths[] = $variantPath;
                imagedestroy($variant);
            }
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($paths);
            throw $exception;
        } finally {
            imagedestroy($source);
        }

        return ['path' => $path, 'paths' => $paths, 'mime' => $outputMime, 'extension' => $extension, 'width' => $targetWidth, 'height' => $targetHeight];
    }

    private function createImageResource(string $path, string $mime): \GdImage
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => false,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('کتابخانه تصویر سرور از این فرمت پشتیبانی نمی‌کند.');
        }

        return $image;
    }

    private function applyExifOrientation(\GdImage $image, string $path, string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) ((@exif_read_data($path)['Orientation'] ?? 1));
        $angle = match ($orientation) {
            3 => 180, 6 => -90, 8 => 90, default => 0
        };
        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function resize(\GdImage $source, int $width, int $height, int $targetWidth, int $targetHeight): \GdImage
    {
        $copy = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagecopyresampled($copy, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $copy;
    }

    private function encode(\GdImage $image, string $mime): string
    {
        ob_start();
        $success = match ($mime) {
            'image/webp' => imagewebp($image, null, (int) config('media.webp_quality', 82)),
            'image/jpeg' => imagejpeg($image, null, 85),
            'image/png' => imagepng($image, null, 7),
            'image/avif' => function_exists('imageavif') && imageavif($image, null, 82),
            default => false,
        };
        $content = ob_get_clean();

        if (! $success || ! is_string($content) || $content === '') {
            throw new RuntimeException('رمزگذاری تصویر به فرمت خروجی ناموفق بود.');
        }

        return $content;
    }

    private function putOrFail(string $disk, string $path, string $content): void
    {
        if (! Storage::disk($disk)->put($path, $content)) {
            throw new RuntimeException('ذخیره فایل تصویر روی دیسک ناموفق بود.');
        }
    }
}
