<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicFileUrl
{
    public static function make(?string $path, string $fallback = 'assets/img/asnaf-gorgan-default.jpg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback === '' ? '' : asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $localPath = self::localMediaPathFromUrl($path);

            return $localPath === null ? $path : route('media.public', ['path' => $localPath]);
        }

        if (Str::startsWith($path, '/') && ! Str::startsWith($path, ['/storage/', '/media-files/', '/uploaded-media/'])) {
            return $path;
        }

        return route('media.public', ['path' => self::normalizeStoragePath($path)]);
    }

    public static function normalizeStoragePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = parse_url($path, PHP_URL_PATH) ?: $path;

        return ltrim((string) preg_replace('#^/?(public/|storage/|media-files/|uploaded-media/)#', '', $path), '/');
    }

    public static function localMediaPathFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (Str::startsWith($path, '/media/')) {
            return ltrim(substr($path, strlen('/media/')), '/');
        }

        if (! Str::startsWith($path, ['/storage/', '/media-files/', '/uploaded-media/'])) {
            return null;
        }

        return self::normalizeStoragePath($path);
    }

    public static function sameApplicationStoragePath(string $url): ?string
    {
        return self::localMediaPathFromUrl($url);
    }
}
