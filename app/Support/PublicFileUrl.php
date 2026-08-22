<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicFileUrl
{
    private const APPLICATION_MEDIA_HOSTS = ['aliche.ir', 'gorganasnaf.ir'];

    private static array $existenceCache = [];

    public static function make(?string $path, ?string $fallback = null): string
    {
        $fallback ??= (string) config('media.placeholder', 'assets/img/asnaf-gorgan-default.jpg');
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

    public static function srcset(?string $path): ?string
    {
        $path = self::normalizeStoragePath((string) $path);
        if ($path === '' || strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'gif') {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $base = substr($path, 0, -strlen($extension) - 1);
        $items = [];

        foreach ((array) config('media.variant_widths', [400, 800]) as $width) {
            $variant = $base.'-'.(int) $width.'w.'.$extension;
            if (self::exists($variant)) {
                $items[] = self::publicStorageUrl($variant).' '.(int) $width.'w';
            }
        }

        return $items === [] ? null : implode(', ', $items);
    }

    public static function exists(string $path): bool
    {
        $path = self::normalizeStoragePath($path);
        if ($path === '') {
            return false;
        }

        if (array_key_exists($path, self::$existenceCache)) {
            return self::$existenceCache[$path];
        }

        foreach (self::storageRoots() as $root) {
            if (is_file(rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path)) {
                return self::$existenceCache[$path] = true;
            }
        }

        return self::$existenceCache[$path] = false;
    }

    /** @return array<int, string> */
    public static function storageRoots(): array
    {
        try {
            $activeDiskRoot = Storage::disk('public')->path('');
        } catch (\Throwable) {
            $activeDiskRoot = null;
        }

        return array_values(array_unique(array_filter([
            $activeDiskRoot,
            config('filesystems.disks.public.root'),
            storage_path('app/public'),
            public_path('storage'),
            public_path('media-files'),
            public_path('uploaded-media'),
            base_path('public_html/storage'),
            base_path('public_html/media-files'),
            base_path('public_html/uploaded-media'),
            base_path('public_html/public_html/storage'),
            base_path('public_html/public_html/media-files'),
        ], fn ($root) => is_string($root) && $root !== '')));
    }

    public static function normalizeStoragePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = rawurldecode($path);
        $path = (string) preg_replace('#^/(?:media/)#', '', $path);
        $path = (string) preg_replace('#^/?(?:(?:public_html/)+)?(?:public/|storage/|media-files/|uploaded-media/)#', '', $path);
        $path = ltrim($path, '/');

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
