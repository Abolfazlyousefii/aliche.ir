<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeImageUpload implements ValidationRule
{
    /** @var array<string, array<int, string>> */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'image/gif' => ['gif'],
        'image/avif' => ['avif'],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('فایل تصویر به‌درستی دریافت نشد.');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($value->getRealPath()) ?: '';
        $imageInfo = @getimagesize($value->getRealPath());
        $allowedExtensions = self::MIME_EXTENSIONS[$mime] ?? [];

        if ($imageInfo === false || ! in_array($extension, $allowedExtensions, true)) {
            $fail('فایل انتخاب‌شده یک تصویر معتبر با پسوند و نوع واقعی هماهنگ نیست.');

            return;
        }

        $maxPixels = max(1, (int) config('media.max_pixels', 40000000));
        if ((int) $imageInfo[1] > 0 && (int) $imageInfo[0] > intdiv($maxPixels, (int) $imageInfo[1])) {
            $fail('ابعاد تصویر بیش از حد بزرگ است؛ لطفاً تصویر کوچک‌تری بارگذاری کنید.');

            return;
        }

        if ($mime === 'image/avif' && (! function_exists('imagecreatefromavif') || ! function_exists('imageavif'))) {
            $fail('فرمت AVIF در این سرور پشتیبانی نمی‌شود؛ لطفاً JPG، PNG یا WebP بارگذاری کنید.');
        }
    }
}
