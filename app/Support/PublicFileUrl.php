<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicFileUrl
{
    private const APPLICATION_MEDIA_HOSTS = ['aliche.ir', 'gorganasnaf.ir'];

    public static function make(?string $path, string $fallback = 'assets/img/asnaf-gorgan-default.jpg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback === '' ? '' : asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $localPath = self::localMediaPathFromUrl($path);

            return $localPath === null ? $path : self::publicStorageUrl($localPath);
        }

        if (Str::startsWith($path, '/') && ! Str::startsWith($path, ['/media/', '/storage/', '/media-files/', '/uploaded-media/', '/public/'])) {
            return $path;
        }

        $url = self::publicStorageUrl($path);

        return $url !== '' ? $url : ($fallback === '' ? '' : asset($fallback));
    }

    public static function normalizeStoragePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = rawurldecode($path);
        $path = ltrim((string) preg_replace('#^/?(public/|storage/|media/|media-files/|uploaded-media/)#', '', $path), '/');

        return str_contains($path, '..') ? '' : $path;
    }

    public static function localMediaPathFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';

        if (isset($parts['host']) && ! self::isApplicationHost((string) $parts['host'])) {
            return null;
        }

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (Str::startsWith($path, '/media/')) {
            return self::normalizeStoragePath($path);
        }

        if (! Str::startsWith($path, ['/storage/', '/media-files/', '/uploaded-media/'])) {
            return null;
        }

        return self::normalizeStoragePath($path);
    }

    private static function publicStorageUrl(string $path): string
    {
        $path = self::normalizeStoragePath($path);

        return $path === '' ? '' : Storage::disk('public')->url($path);
    }

    public static function sameApplicationStoragePath(string $url): ?string
    {
        return self::localMediaPathFromUrl($url);
    }

    private static function isApplicationHost(string $host): bool
    {
        $normalizeHost = static fn (string $value): string => preg_replace('/^www\./i', '', strtolower($value)) ?: '';
        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $hosts = self::APPLICATION_MEDIA_HOSTS;

        if (is_string($configuredHost) && $configuredHost !== '') {
            $hosts[] = $configuredHost;
        }

        return in_array($normalizeHost($host), array_map($normalizeHost, $hosts), true);
    }
}
