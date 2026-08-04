<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Services\DailyNewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DailyNewsTest extends TestCase
{
    use RefreshDatabase;

    private function post(array $data): Post
    {
        return Post::create(array_merge([
            'title' => 'خبر تست', 'slug' => fake()->unique()->slug(), 'type' => 'news', 'status' => 'published',
            'published_at' => now()->setTime(10, 0), 'is_active' => true,
        ], $data));
    }

    public function test_home_displays_only_today_published_posts_ordered_by_publication_time(): void
    {
        $old = $this->post(['title' => 'خبر قدیمی امروز', 'slug' => 'old-today', 'published_at' => now()->setTime(9, 0)]);
        $new = $this->post(['title' => 'خبر جدید امروز', 'slug' => 'new-today', 'published_at' => now()->setTime(12, 0)]);
        $this->post(['title' => 'پیش نویس', 'slug' => 'draft-news', 'status' => 'draft']);
        $this->post(['title' => 'آرشیو', 'slug' => 'archived-news', 'status' => 'archived']);
        $this->post(['title' => 'آینده', 'slug' => 'future-news', 'published_at' => now()->addHour()]);
        $this->post(['title' => 'دیروز', 'slug' => 'yesterday-news', 'published_at' => now()->subDay()]);

        $response = $this->get(route('home'));
        $response->assertOk()->assertSee('اخبار روزانه')->assertSee($new->title)->assertSee($old->title)
            ->assertDontSee('پیش نویس')->assertDontSee('آرشیو')->assertDontSee('آینده')->assertDontSee('دیروز');
        $response->assertSeeInOrder([$new->title, $old->title]);
    }

    public function test_daily_news_page_shows_previous_day_and_empty_message_and_disables_next_after_today(): void
    {
        $yesterday = now()->subDay()->setTime(11, 15);
        $post = $this->post(['title' => 'خبر دیروز', 'slug' => 'yesterday-post', 'published_at' => $yesterday]);
        $date = app(DailyNewsService::class)->jalaliParam($yesterday->copy()->startOfDay());

        $this->get(route('daily-news.index', ['date' => $date]))->assertOk()->assertSee('خبر دیروز')->assertSee(route('posts.show', $post->slug));
        $this->get(route('daily-news.index'))->assertOk()->assertSee('برای این روز هنوز خبری منتشر نشده است.')->assertSee('disabled', false);
    }

    public function test_placeholder_category_badges_links_and_cache_flush(): void
    {
        Cache::flush();
        $category = Category::create(['title' => 'اتاق اصناف', 'slug' => 'guild-room', 'type' => 'news', 'is_active' => true]);
        $post = $this->post(['title' => 'خبر بدون تصویر', 'slug' => 'no-image', 'category_id' => $category->id, 'is_important' => true, 'is_featured' => true]);

        $this->get(route('home'))->assertOk()
            ->assertSee('asnaf-gorgan-default.jpg')->assertSee('اتاق اصناف')->assertSee('فوری')->assertSee('مهم')->assertSee('ویژه')
            ->assertSee(route('posts.show', $post->slug));

        $key = app(DailyNewsService::class)->cacheKey(now()->startOfDay(), 'home');
        $this->assertTrue(Cache::has($key));
        $post->update(['title' => 'عنوان ویرایش شده']);
        $this->assertFalse(Cache::has($key));
    }
}
