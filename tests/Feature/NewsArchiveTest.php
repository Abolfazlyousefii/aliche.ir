<?php

namespace Tests\Feature;

use App\Models\GuildUnion;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_only_contains_published_active_news(): void
    {
        $news = $this->createPost('خبر قابل نمایش', 'visible-news');
        $this->createPost('اطلاعیه داخل جدول نوشته‌ها', 'post-announcement', ['type' => 'announcement']);
        $this->createPost('مقاله منتشرشده', 'published-article', ['type' => 'article']);
        $this->createPost('خبر پیش‌نویس', 'draft-news', ['status' => 'draft']);
        $this->createPost('خبر غیرفعال', 'inactive-news', ['is_active' => false]);
        $this->createPost('خبر آینده', 'future-news', ['published_at' => now()->addDay()]);

        $response = $this->get(route('posts.index'));

        $response->assertOk()
            ->assertSee($news->title)
            ->assertDontSee('اطلاعیه داخل جدول نوشته‌ها')
            ->assertDontSee('مقاله منتشرشده')
            ->assertDontSee('خبر پیش‌نویس')
            ->assertDontSee('خبر غیرفعال')
            ->assertDontSee('خبر آینده')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 1);
    }

    public function test_search_is_scoped_to_news(): void
    {
        $this->createPost('گزارش ویژه بازار گرگان', 'matching-news');
        $this->createPost('گزارش ویژه بازار در اطلاعیه', 'matching-announcement', ['type' => 'announcement']);
        $this->createPost('خبر نامرتبط', 'unrelated-news');

        $response = $this->get(route('posts.index', ['search' => 'گزارش ویژه بازار']));

        $response->assertOk()
            ->assertSee('گزارش ویژه بازار گرگان')
            ->assertDontSee('گزارش ویژه بازار در اطلاعیه')
            ->assertDontSee('خبر نامرتبط')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 1);
    }

    public function test_union_filter_and_search_are_combined(): void
    {
        $selectedUnion = $this->union('اتحادیه منتخب', 'selected-union');
        $otherUnion = $this->union('اتحادیه دیگر', 'other-union');
        $this->createPost('خبر هدف اتحادیه منتخب', 'selected-matching-news', ['union_id' => $selectedUnion->id]);
        $this->createPost('خبر نامرتبط اتحادیه منتخب', 'selected-unrelated-news', ['union_id' => $selectedUnion->id]);
        $this->createPost('خبر هدف اتحادیه دیگر', 'other-matching-news', ['union_id' => $otherUnion->id]);
        $this->createPost('خبر هدف در اطلاعیه', 'selected-matching-announcement', [
            'type' => 'announcement',
            'union_id' => $selectedUnion->id,
        ]);

        $response = $this->get(route('posts.index', [
            'search' => 'خبر هدف',
            'union_id' => $selectedUnion->id,
        ]));

        $response->assertOk()
            ->assertSee('خبر هدف اتحادیه منتخب')
            ->assertDontSee('خبر نامرتبط اتحادیه منتخب')
            ->assertDontSee('خبر هدف اتحادیه دیگر')
            ->assertDontSee('خبر هدف در اطلاعیه')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 1);
    }

    public function test_valid_union_without_news_has_an_empty_result_and_invalid_union_is_ignored(): void
    {
        $unionWithoutNews = $this->union('اتحادیه بدون خبر', 'empty-union');
        $this->createPost('خبر عمومی موجود', 'general-news');

        $this->get(route('posts.index', ['union_id' => $unionWithoutNews->id]))
            ->assertOk()
            ->assertSee('خبری پیدا نشد')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 0);

        $this->get(route('posts.index', ['union_id' => 'invalid']))
            ->assertOk()
            ->assertSee('خبر عمومی موجود')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 1);
    }

    public function test_card_renders_safely_without_an_image_or_excerpt_and_with_a_long_title(): void
    {
        $title = str_repeat('عنوان بلند خبر ', 14);
        $this->createPost($title, 'long-news-without-media', [
            'excerpt' => null,
            'body' => '<script>alert("unsafe")</script><p>متن امن خبر</p>',
            'featured_image' => null,
        ]);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('متن امن خبر')
            ->assertSee('class="news-archive-card-media"', false)
            ->assertDontSee('<script>alert("unsafe")</script>', false);
    }

    public function test_pagination_only_counts_news_and_preserves_filters(): void
    {
        $union = $this->union('اتحادیه صفحه‌بندی', 'pagination-union');

        foreach (range(1, 13) as $index) {
            $this->createPost("خبر صفحه‌بندی {$index}", "paginated-news-{$index}", [
                'union_id' => $union->id,
                'published_at' => now()->subMinutes($index),
            ]);
        }

        $this->createPost('اطلاعیه خارج از صفحه‌بندی', 'pagination-announcement', [
            'type' => 'announcement',
            'union_id' => $union->id,
        ]);

        $response = $this->get(route('posts.index', [
            'search' => 'خبر صفحه‌بندی',
            'union_id' => $union->id,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertDontSee('اطلاعیه خارج از صفحه‌بندی')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 13
                && $posts->currentPage() === 2
                && $posts->count() === 1)
            ->assertSee('search=%D8%AE%D8%A8%D8%B1%20%D8%B5%D9%81%D8%AD%D9%87%E2%80%8C%D8%A8%D9%86%D8%AF%DB%8C', false)
            ->assertSee('union_id='.$union->id, false);
    }

    public function test_ajax_pagination_returns_the_shared_results_partial_and_preserves_filters(): void
    {
        $union = $this->union('اتحادیه ایجکس', 'ajax-union');

        foreach (range(1, 25) as $index) {
            $this->createPost("خبر ایجکس {$index}", "ajax-news-{$index}", [
                'union_id' => $union->id,
                'published_at' => now()->subMinutes($index),
            ]);
        }

        $this->createPost('اطلاعیه ایجکس', 'ajax-announcement', [
            'type' => 'announcement',
            'union_id' => $union->id,
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('posts.index', [
            'search' => 'خبر ایجکس',
            'union_id' => $union->id,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 3)
            ->assertJsonPath('total', 25)
            ->assertJsonPath('from', 13)
            ->assertJsonPath('to', 24)
            ->assertJsonStructure(['html', 'current_page', 'last_page', 'total', 'from', 'to', 'url']);

        $html = $response->json('html');
        $this->assertStringContainsString('data-news-archive-results', $html);
        $this->assertStringContainsString('news-archive-pagination', $html);
        $this->assertStringContainsString('search=%D8%AE%D8%A8%D8%B1%20%D8%A7%DB%8C%D8%AC%DA%A9%D8%B3', $html);
        $this->assertStringContainsString('union_id='.$union->id, $html);
        $this->assertStringNotContainsString('اطلاعیه ایجکس', $html);
    }

    public function test_normal_and_ajax_last_page_keep_server_side_pagination_available(): void
    {
        foreach (range(1, 25) as $index) {
            $this->createPost("خبر صفحه آخر {$index}", "last-page-news-{$index}", [
                'published_at' => now()->subMinutes($index),
            ]);
        }

        $this->get(route('posts.index', ['page' => 2]))
            ->assertOk()
            ->assertViewIs('frontend.posts.index')
            ->assertSee('data-news-archive-results', false)
            ->assertSee('href="'.route('posts.index').'"', false);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('posts.index', ['page' => 3]));

        $response->assertOk()
            ->assertJsonPath('current_page', 3)
            ->assertJsonPath('last_page', 3)
            ->assertJsonPath('total', 25)
            ->assertJsonPath('from', 25)
            ->assertJsonPath('to', 25);

        $this->assertStringContainsString('صفحه بعدی در دسترس نیست', $response->json('html'));
    }

    public function test_middle_page_pagination_uses_a_compact_window_with_ellipses(): void
    {
        foreach (range(1, 145) as $index) {
            $this->createPost("خبر پنجره صفحه‌بندی {$index}", "windowed-news-{$index}", [
                'published_at' => now()->subMinutes($index),
            ]);
        }

        $html = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('posts.index', ['page' => 5]))
            ->assertOk()
            ->assertJsonPath('current_page', 5)
            ->assertJsonPath('last_page', 13)
            ->json('html');

        $this->assertSame(2, substr_count($html, 'news-archive-page-ellipsis'));
        $this->assertStringContainsString('page=3', $html);
        $this->assertStringContainsString('page=7', $html);
        $this->assertStringContainsString('page=13', $html);
        $this->assertStringNotContainsString('page=8', $html);
    }

    private function createPost(string $title, string $slug, array $attributes = []): Post
    {
        return Post::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'خلاصه امن خبر برای آزمون آرشیو.',
            'body' => '<p>متن خبر</p>',
            'type' => 'news',
            'status' => 'published',
            'published_at' => now()->subHour(),
            'is_active' => true,
        ], $attributes));
    }

    private function union(string $name, string $slug): GuildUnion
    {
        return GuildUnion::query()->create([
            'name' => $name,
            'title' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
}
