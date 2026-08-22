<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\DailyNewsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DailyNewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-11 12:00:00', config('app.timezone')));
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createNewsPost(array $data): Post
    {
        return Post::create(array_merge([
            'title' => 'خبر تست', 'slug' => fake()->unique()->slug(), 'type' => 'news', 'status' => 'published',
            'published_at' => now()->setTime(10, 0), 'is_active' => true,
        ], $data));
    }

    public function test_daily_news_page_displays_only_selected_day_published_posts_ordered_by_publication_time(): void
    {
        $old = $this->createNewsPost(['title' => 'خبر قدیمی امروز', 'slug' => 'old-today', 'published_at' => now()->setTime(9, 0)]);
        $new = $this->createNewsPost(['title' => 'خبر جدید امروز', 'slug' => 'new-today', 'published_at' => now()->setTime(11, 0)]);
        $draft = $this->createNewsPost(['title' => 'خبر پیش‌نویس ویژه تست', 'slug' => 'draft-news', 'status' => 'draft']);
        $archived = $this->createNewsPost(['title' => 'خبر آرشیوشده ویژه تست', 'slug' => 'archived-news', 'status' => 'archived']);
        $future = $this->createNewsPost(['title' => 'خبر آینده ویژه تست', 'slug' => 'future-news', 'published_at' => now()->addHour()]);
        $yesterday = $this->createNewsPost(['title' => 'خبر روز گذشته ویژه تست', 'slug' => 'yesterday-news', 'published_at' => now()->subDay()]);

        $response = $this->get(route('daily-news.index'));
        $response->assertOk()->assertSee($new->title)->assertSee($old->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($archived->title)
            ->assertDontSee($future->title)
            ->assertDontSee($yesterday->title);
        $response->assertSeeInOrder([$new->title, $old->title]);
    }

    public function test_daily_news_page_shows_previous_day_and_empty_message_and_disables_next_after_today(): void
    {
        $yesterday = now()->subDay()->setTime(11, 15);
        $post = $this->createNewsPost(['title' => 'خبر دیروز', 'slug' => 'yesterday-post', 'published_at' => $yesterday]);
        $date = app(DailyNewsService::class)->jalaliParam($yesterday->copy()->startOfDay());

        $this->get(route('daily-news.index', ['date' => $date]))->assertOk()->assertSee('خبر دیروز')->assertSee(route('posts.show', $post->slug));
        $this->get(route('daily-news.index'))->assertOk()->assertSee('برای این روز هنوز خبری منتشر نشده است.')->assertSee('disabled', false);
    }

    public function test_daily_news_card_renders_fallback_image_category_and_status_badges(): void
    {
        $category = $this->createNewsCategory();
        $post = $this->createNewsPost(['title' => 'خبر بدون تصویر', 'slug' => 'no-image', 'category_id' => $category->id, 'is_important' => true, 'is_featured' => true]);

        $this->get(route('daily-news.index'))->assertOk()
            ->assertSee('asnaf-gorgan-default.jpg')
            ->assertSee($category->title)
            ->assertSee('فوری')
            ->assertSee('مهم')
            ->assertSee('ویژه');
    }

    public function test_daily_news_card_links_to_post_detail(): void
    {
        $post = $this->createNewsPost(['title' => 'خبر دارای لینک', 'slug' => 'linked-daily-news']);

        $this->get(route('daily-news.index'))->assertOk()
            ->assertSee(route('posts.show', $post->slug), false);
    }

    public function test_daily_news_service_caches_requested_day_count(): void
    {
        $date = now()->startOfDay();
        $this->createNewsPost(['slug' => 'cached-daily-news']);
        $service = app(DailyNewsService::class);
        $key = $service->cacheKey($date, 'count');

        $this->assertSame(1, $service->count($date));
        $this->assertTrue(Cache::has($key));
        $this->assertSame(1, Cache::get($key));
    }

    public function test_daily_news_cache_is_flushed_when_relevant_post_changes(): void
    {
        $date = now()->startOfDay();
        $post = $this->createNewsPost(['slug' => 'invalidated-daily-news']);
        $service = app(DailyNewsService::class);
        $key = $service->cacheKey($date, 'count');

        $service->count($date);
        $this->assertTrue(Cache::has($key));

        $post->update(['title' => 'عنوان ویرایش شده']);

        $this->assertFalse(Cache::has($key));
    }

    private function createNewsCategory(): Category
    {
        $category = Category::query()->create([
            'title' => 'اتاق اصناف',
            'slug' => 'guild-room',
            'type' => 'news',
            'is_active' => true,
        ]);

        // SQLite retains the legacy FK while the current relation reads categories.
        PostCategory::query()->create([
            'id' => $category->id,
            'title' => $category->title,
            'slug' => 'legacy-'.$category->slug,
            'is_active' => true,
        ]);

        return $category;
    }
}
