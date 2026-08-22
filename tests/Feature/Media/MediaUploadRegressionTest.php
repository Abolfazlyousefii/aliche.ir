<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->signInAsSuperAdmin();
    }

    public function test_jpg_upload_creates_a_valid_media_record_file_and_public_url(): void
    {
        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('camera.jpg', 1280, 720)],
        ])->assertCreated()->assertJsonCount(1, 'items');

        $media = Media::query()->sole();

        Storage::disk('public')->assertExists($media->path);
        $this->assertSame(function_exists('imagewebp') ? 'image/webp' : 'image/jpeg', $media->mime_type);
        $this->assertSame(function_exists('imagewebp') ? 'webp' : 'jpg', $media->extension);
        $this->assertStringStartsWith('/storage/', $media->url);
        $this->assertSame([1280, 720], [$media->width, $media->height]);
    }

    public function test_png_upload_is_converted_without_losing_dimensions(): void
    {
        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('graphic.png', 900, 600)],
        ])->assertCreated();

        $media = Media::query()->sole();
        $expectedMime = function_exists('imagewebp') ? 'image/webp' : 'image/png';

        $this->assertSame($expectedMime, $media->mime_type);
        $this->assertSame([900, 600], [$media->width, $media->height]);
        $this->assertSame($expectedMime, (string) (new \finfo(FILEINFO_MIME_TYPE))->file(Storage::disk('public')->path($media->path)));
    }

    public function test_existing_webp_is_not_reencoded_or_changed(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is unavailable.');
        }

        $image = imagecreatetruecolor(320, 180);
        ob_start();
        imagewebp($image, null, 91);
        $contents = ob_get_clean();
        imagedestroy($image);

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->createWithContent('optimized.webp', $contents)],
        ])->assertCreated();

        $media = Media::query()->sole();

        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame($contents, Storage::disk('public')->get($media->path));
        $this->assertSame(hash('sha256', $contents), $media->hash);
    }

    public function test_gif_is_preserved_byte_for_byte_to_protect_animation(): void
    {
        $contents = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->createWithContent('animation.gif', $contents)],
        ])->assertCreated();

        $media = Media::query()->sole();

        $this->assertSame('image/gif', $media->mime_type);
        $this->assertSame('gif', $media->extension);
        $this->assertSame($contents, Storage::disk('public')->get($media->path));
    }

    public function test_executable_disguised_files_php_files_and_oversized_files_are_rejected_without_artifacts(): void
    {
        foreach (['shell.php.jpg', 'shell.php'] as $name) {
            $this->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->createWithContent($name, '<?php echo "owned";')],
            ])->assertUnprocessable()->assertJsonValidationErrors('files.0');
        }

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->create('oversized.jpg', config('media.max_upload_kilobytes') + 1, 'image/jpeg')],
        ])->assertUnprocessable()->assertJsonValidationErrors('files.0');

        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_6000_by_4000_image_is_resized_with_ratio_variants_and_no_upscale(): void
    {
        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('six-thousand.jpg', 6000, 4000)],
        ])->assertCreated();

        $large = Media::query()->sole();
        $this->assertSame([1920, 1280], [$large->width, $large->height]);
        $this->assertEqualsWithDelta(1.5, $large->width / $large->height, 0.001);
        $this->assertSame('webp', $large->extension);
        Storage::disk('public')->assertExists($large->path);
        Storage::disk('public')->assertExists(str_replace('.webp', '-400w.webp', $large->path));
        Storage::disk('public')->assertExists(str_replace('.webp', '-800w.webp', $large->path));

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('small.jpg', 160, 90)],
        ])->assertCreated();

        $small = Media::query()->latest('id')->firstOrFail();
        $this->assertSame([160, 90], [$small->width, $small->height]);
        Storage::disk('public')->assertMissing(str_replace('.webp', '-400w.webp', $small->path));
    }
}
