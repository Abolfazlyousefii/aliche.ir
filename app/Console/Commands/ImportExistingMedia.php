<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportExistingMedia extends Command
{
    protected $signature = 'media:import-existing';

    protected $description = 'Import existing images into media library without moving files';

    public function handle(): int
    {
        $roots = [
            [storage_path('app/public'), ''],
            [public_path('storage'), ''],
            [public_path('media-files'), ''],
            [public_path('uploaded-media'), ''],
            [base_path('public_html/storage'), ''],
            [base_path('public_html/media-files'), ''],
            [base_path('public_html/uploaded-media'), ''],
            [base_path('public_html/public_html/storage'), ''],
            [base_path('public_html/public_html/media-files'), ''],
            [public_path('assets/img'), 'assets/img'],
        ];

        $checked = 0;
        $imported = 0;
        $dupes = 0;

        foreach ($roots as [$root, $prefix]) {
            if (! is_dir($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                $extension = strtolower($file->getExtension());
                if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg'], true)) {
                    continue;
                }

                $checked++;
                $relative = str_replace('\\', '/', $file->getRelativePathname());
                $path = trim($prefix.'/'.$relative, '/');
                $hash = hash_file('sha256', $file->getPathname());

                if (Media::query()->where('path', $path)->orWhere('hash', $hash)->exists()) {
                    $dupes++;

                    continue;
                }

                [$width, $height] = @getimagesize($file->getPathname()) ?: [null, null];

                Media::query()->create([
                    'file_name' => $file->getFilename(),
                    'original_name' => $file->getFilename(),
                    'path' => $path,
                    'disk' => 'public',
                    'mime_type' => File::mimeType($file->getPathname()),
                    'extension' => $extension,
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                    'title' => $file->getBasename('.'.$extension),
                    'hash' => $hash,
                ]);

                $imported++;
            }
        }

        $this->info("checked={$checked} imported={$imported} duplicates={$dupes}");

        return self::SUCCESS;
    }
}
