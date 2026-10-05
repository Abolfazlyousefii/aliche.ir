<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\TourismPlace;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SearchMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_news_search_uses_featured_media_accessor(): void
    {
        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('featured.jpg', 640, 360),
            'posts/featured'
        );

        Post::query()->create([
            'title' => 'خبر رسانه ویژه',
            'slug' => 'search-featured-media',
            'body' => '<p>متن خبر رسانه ویژه</p>',
            'type' => 'news',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'featured_media_id' => $media->id,
            'featured_image' => null,
            'is_active' => true,
        ]);

        $this->get(route('search', ['q' => 'رسانه ویژه']))
            ->assertOk()
            ->assertViewHas('results', function (array $results) use ($media): bool {
                return data_get($results, 'news.items.0.image') === $media->url;
            });
    }

    public function test_tourism_search_prefers_valid_directory_image_over_legacy_placeholder(): void
    {
        $media = app(MediaLibraryService::class)->storeImage(
            UploadedFile::fake()->image('tourism.jpg', 640, 360),
            'tourism/cards'
        );

        $place = TourismPlace::query()->create([
            'title' => 'جاذبه جستجوی تصویر',
            'slug' => 'search-tourism-image',
            'short_description' => 'مکان گردشگری برای آزمون جستجو',
            'image' => $media->path,
            'featured_image' => 'assets/img/asnaf-gorgan-default.jpg',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $this->get(route('search', ['q' => 'جستجوی تصویر']))
            ->assertOk()
            ->assertViewHas('results', function (array $results) use ($place): bool {
                return data_get($results, 'tourism.items.0.image') === $place->directory_image_url;
            });
    }
}
