<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    /** @param array<int, string> $directories */
    public static function ensureDirectories(array $directories): void
    {
        foreach ($directories as $directory) {
            $directory = trim(str_replace('\\', '/', $directory), '/');

            if ($directory !== '' && ! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
        }

        self::ensureStorageLink();
    }

    public static function ensureStorageLink(): void
    {
        $target = config('filesystems.disks.public.root');

        if (! is_string($target) || $target === '') {
            return;
        }

        foreach ([public_path('storage'), public_path('media'), public_path('media-files')] as $link) {
            self::ensureSymlink($link, $target);
        }
    }

    private static function ensureSymlink(string $link, string $target): void
    {
        if (is_link($link)) {
            return;
        }

        if (file_exists($link)) {
            if (realpath($link) === realpath($target) || ! self::isEmptyPlaceholderDirectory($link)) {
                return;
            }

            @rmdir($link);
        }

        @symlink($target, $link);

        if (! is_link($link) && ! file_exists($link)) {
            @mkdir($link, 0755, true);
        }
    }

    private static function isEmptyPlaceholderDirectory(string $path): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        $items = array_values(array_diff(scandir($path) ?: [], ['.', '..']));

        return $items === [] || $items === ['.gitignore'];
    }
}
