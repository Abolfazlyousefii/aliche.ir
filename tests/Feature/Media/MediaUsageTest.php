<?php

namespace Tests\Feature\Media;

use App\Models\Post;
use App\Models\TourismPlace;
use App\Models\UnionType;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUsageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_tourism_card_and_gallery_references_protect_media_from_deletion(): void
    {
        $card = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('card.jpg', 640, 360),
            'tourism/cards'
        );
        $gallery = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('gallery.jpg', 640, 360),
            'tourism/gallery'
        );

        TourismPlace::query()->create([
            'title' => 'جاذبه آزمون',
            'slug' => 'media-usage-tourism',
            'image' => $card->path,
            'gallery' => [['path' => $gallery->path, 'caption' => 'تصویر']],
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'published',
            'published_at' => now(),
            'is_active' => true,
        ]);

        $this->assertTrue($card->fresh()->inUse());
        $this->assertTrue($gallery->fresh()->inUse());
    }

    public function test_rich_text_reference_protects_media_from_deletion(): void
    {
        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('embedded.jpg', 640, 360),
            'rich-text/images'
        );

        Post::query()->create([
            'title' => 'خبر دارای تصویر داخلی',
            'slug' => 'embedded-media-post',
            'body' => '<p><img src="/storage/'.$media->path.'" alt=""></p>',
            'type' => 'news',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $this->assertTrue($media->fresh()->inUse());
    }

    public function test_union_type_image_reference_protects_media_from_deletion(): void
    {
        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('union-type.jpg', 640, 360),
            'union-types/images'
        );

        UnionType::query()->create([
            'title' => 'نوع اتحادیه آزمون',
            'slug' => 'media-usage-union-type',
            'image' => $media->path,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($media->fresh()->inUse());
    }
}
