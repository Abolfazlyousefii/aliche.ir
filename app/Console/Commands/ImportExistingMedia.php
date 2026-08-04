<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportExistingMedia extends Command
{
    protected $signature = 'media:import-existing';

    protected $description = 'Import existing images into media library without moving files';

    public function handle(): int
    {
        $roots = [
            'storage/app/public' => 'public',
            'public/media-files' => 'public',
            'public/uploads' => 'public',
            'public/images' => 'public',
            'public/assets/img' => 'public',
            'uploads/news' => 'public',
            'storage' => 'public',
        ];

        $checked = 0;
        $imported = 0;
        $dupes = 0;

        foreach ($roots as $root => $disk) {
            $abs = base_path($root);

            if (! is_dir($abs)) {
                continue;
            }

            foreach (File::allFiles($abs) as $file) {
                $ext = strtolower($file->getExtension());

                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true)) {
                    continue;
                }

                $checked++;
                $path = str_starts_with($root, 'storage/app/public')
                    ? Str::after($file->getPathname(), storage_path('app/public').DIRECTORY_SEPARATOR)
                    : '/'.Str::after($file->getPathname(), public_path().DIRECTORY_SEPARATOR);
                $path = str_replace('\\', '/', $path);
                $hash = hash_file('sha256', $file->getPathname());

                if (Media::where('path', $path)->orWhere('hash', $hash)->exists()) {
                    $dupes++;
                    continue;
                }

                [$width, $height] = @getimagesize($file->getPathname()) ?: [null, null];

                Media::create([
                    'file_name' => $file->getFilename(),
                    'original_name' => $file->getFilename(),
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => File::mimeType($file->getPathname()),
                    'extension' => $ext,
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                    'title' => $file->getBasename('.'.$ext),
                    'hash' => $hash,
                ]);

                $imported++;
            }
        }

        $this->info("checked={$checked} imported={$imported} duplicates={$dupes}");

        return self::SUCCESS;
    }
}
