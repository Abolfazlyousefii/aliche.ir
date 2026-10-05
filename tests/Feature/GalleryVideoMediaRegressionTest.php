<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryVideoMediaRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_gallery_uses_first_existing_image_when_registered_cover_is_missing(): void
    {
        Storage::disk('public')->put('galleries/real-image.jpg', 'image');

        $gallery = Gallery::query()->create([
            'title' => 'گالری تست',
            'slug' => 'gallery-fallback-cover',
            'description' => 'گالری دارای کاور شکسته و تصویر داخلی سالم',
            'cover_image' => 'galleries/missing-cover.jpg',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        GalleryImage::query()->create([
            'gallery_id' => $gallery->id,
            'image' => 'galleries/real-image.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('galleries.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url('galleries/real-image.jpg'), false)
            ->assertSee('گالری تست');
    }

    public function test_gallery_lightbox_exposes_keyboard_accessible_controls(): void
    {
        Storage::disk('public')->put('galleries/accessibility.jpg', 'image');

        $gallery = Gallery::query()->create([
            'title' => 'گالری دسترس‌پذیر',
            'slug' => 'accessible-gallery',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        GalleryImage::query()->create([
            'gallery_id' => $gallery->id,
            'image' => 'galleries/accessibility.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('galleries.show', $gallery->slug))
            ->assertOk()
            ->assertSee('class="gallery-thumb"', false)
            ->assertSee('type="button"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('aria-hidden="true"', false);
    }

    public function test_video_pages_use_canonical_cover_and_file_urls(): void
    {
        Storage::disk('public')->put('videos/cover.jpg', 'image');
        Storage::disk('public')->put('videos/demo.webm', 'video');

        $video = Video::query()->create([
            'title' => 'ویدیوی تست',
            'slug' => 'canonical-video-media',
            'cover_image' => 'videos/cover.jpg',
            'video_type' => 'upload',
            'video_file' => 'videos/demo.webm',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('videos.index'))
            ->assertOk()
            ->assertSee($video->cover_image_url, false);

        $this->get(route('videos.show', $video->slug))
            ->assertOk()
            ->assertSee($video->cover_image_url, false)
            ->assertSee($video->video_file_url, false)
            ->assertSee('video/webm', false);
    }
}
