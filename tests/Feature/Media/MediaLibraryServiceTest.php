<?php

namespace Tests\Feature\Media;

use App\Rules\SafeImageUpload;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MediaLibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_jpeg_is_resized_converted_and_given_responsive_variants(): void
    {
        Storage::fake('public');
        config()->set('media.max_width', 1920);
        config()->set('media.variant_widths', [400, 800]);

        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('large-photo.jpg', 2400, 1200),
            'media'
        );

        $this->assertSame('webp', $media->extension);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame(1920, $media->width);
        $this->assertSame(960, $media->height);
        $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/[0-9a-f-]+\.webp$#', $media->path);
        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists(str_replace('.webp', '-400w.webp', $media->path));
        Storage::disk('public')->assertExists(str_replace('.webp', '-800w.webp', $media->path));
    }

    public function test_fake_jpg_extension_is_rejected_by_real_mime_validation(): void
    {
        $validator = Validator::make([
            'image' => UploadedFile::fake()->createWithContent('payload.jpg', '<?php echo "unsafe";'),
        ], [
            'image' => ['file', new SafeImageUpload],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_excessive_pixel_dimensions_are_rejected_before_decoding(): void
    {
        config()->set('media.max_pixels', 5000);
        $validator = Validator::make([
            'image' => UploadedFile::fake()->image('too-many-pixels.jpg', 100, 100),
        ], [
            'image' => ['file', new SafeImageUpload],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_small_image_is_not_upscaled(): void
    {
        Storage::fake('public');

        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('small.png', 320, 180),
            'media'
        );

        $this->assertSame(320, $media->width);
        $this->assertSame(180, $media->height);
        Storage::disk('public')->assertMissing(str_replace('.webp', '-400w.webp', $media->path));
    }

    public function test_gif_is_preserved_without_flattening_or_generated_variants(): void
    {
        Storage::fake('public');
        $contents = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        $file = UploadedFile::fake()->createWithContent('animation.gif', $contents);

        $media = app(MediaLibraryService::class)->storeImage($file, 'media');

        $this->assertSame('gif', $media->extension);
        $this->assertSame($contents, Storage::disk('public')->get($media->path));
        $this->assertNull($media->srcset);
    }

    public function test_existing_small_webp_is_not_reencoded(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is unavailable.');
        }

        Storage::fake('public');
        $image = imagecreatetruecolor(120, 60);
        ob_start();
        imagewebp($image, null, 90);
        $contents = ob_get_clean();
        imagedestroy($image);
        $file = UploadedFile::fake()->createWithContent('ready.webp', $contents);

        $media = app(MediaLibraryService::class)->storeImage($file, 'media');

        $this->assertSame($contents, Storage::disk('public')->get($media->path));
        $this->assertSame(120, $media->width);
        $this->assertSame(60, $media->height);
    }

    public function test_avif_is_accepted_only_when_gd_can_decode_and_encode_it(): void
    {
        if (! function_exists('imageavif') || ! function_exists('imagecreatefromavif')) {
            $this->markTestSkipped('GD AVIF support is unavailable.');
        }

        Storage::fake('public');
        $image = imagecreatetruecolor(160, 90);
        ob_start();
        imageavif($image, null, 80);
        $contents = ob_get_clean();
        imagedestroy($image);

        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->createWithContent('modern.avif', $contents),
            'media'
        );

        $this->assertSame('webp', $media->extension);
        $this->assertSame(160, $media->width);
        $this->assertSame(90, $media->height);
    }

    public function test_deleting_media_removes_its_generated_variants(): void
    {
        Storage::fake('public');
        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('photo.png', 1200, 600),
            'media'
        );
        $paths = app(MediaLibraryService::class)->pathsFor($media->path);

        app(MediaLibraryService::class)->deleteFiles($media);

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
