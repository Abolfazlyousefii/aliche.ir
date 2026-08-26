<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSinglePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_post_places_title_before_image_and_renders_dynamic_keywords_below_content(): void
    {
        $post = $this->post('خبر اصلی آزمون', 'main-post', [
            'featured_image' => 'posts/featured/example.jpg',
            'meta_keywords' => "اصناف، گرگان, اتحادیه ها\nبازار",
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk()
            ->assertViewIs('frontend.posts.show')
            ->assertSee('آخرین اخبار')
            ->assertSee('برچسب‌ها')
            ->assertSee('اصناف')
            ->assertSee('گرگان')
            ->assertSee('اتحادیه ها')
            ->assertSee('بازار')
            ->assertSee(route('posts.index', ['search' => 'اصناف']), false);

        $html = $response->getContent();
        $titlePosition = strpos($html, 'class="single-post-title"');
        $imagePosition = strpos($html, 'class="post-featured-img"');
        $contentPosition = strpos($html, 'class="post-content"');
        $keywordsPosition = strpos($html, 'class="post-keywords-section"');

        $this->assertNotFalse($titlePosition);
        $this->assertNotFalse($imagePosition);
        $this->assertNotFalse($contentPosition);
        $this->assertNotFalse($keywordsPosition);
        $this->assertLessThan($imagePosition, $titlePosition, 'عنوان خبر باید قبل از تصویر شاخص رندر شود.');
        $this->assertLessThan($keywordsPosition, $contentPosition, 'برچسب‌ها باید بعد از محتوای خبر رندر شوند.');
        $this->assertSame(1, substr_count($html, '>برچسب‌ها<'), 'باکس برچسب ثابت سایدبار نباید باقی بماند.');
    }

    public function test_sidebar_receives_only_the_latest_15_other_published_editorial_posts(): void
    {
        $current = $this->post('خبر فعلی', 'current-post', [
            'published_at' => now()->subDays(10),
        ]);

        foreach (range(1, 16) as $index) {
            $this->post(
                sprintf('خبر تازه %02d', $index),
                sprintf('latest-post-%02d', $index),
                ['published_at' => now()->subMinutes($index)]
            );
        }

        $draft = $this->post('خبر پیش نویس', 'draft-post', [
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('posts.show', $current->slug));

        $response->assertOk()
            ->assertViewHas('latestPosts', function ($posts) use ($current, $draft) {
                return $posts->count() === 15
                    && ! $posts->contains('id', $current->id)
                    && ! $posts->contains('id', $draft->id)
                    && $posts->first()?->title === 'خبر تازه 01'
                    && $posts->last()?->title === 'خبر تازه 15'
                    && ! $posts->contains('title', 'خبر تازه 16');
            });
    }

    public function test_archive_search_matches_dynamic_meta_keywords(): void
    {
        $tagged = $this->post('خبر بدون عبارت در عنوان', 'tagged-post', [
            'excerpt' => 'خلاصه عمومی',
            'body' => 'متن عمومی',
            'meta_keywords' => 'بازرگانی، اصناف',
        ]);

        $other = $this->post('خبر دیگر', 'other-post', [
            'excerpt' => 'متن متفاوت',
            'body' => 'بدون کلیدواژه هدف',
            'meta_keywords' => 'خدمات',
        ]);

        $this->get(route('posts.index', ['search' => 'بازرگانی']))
            ->assertOk()
            ->assertSee($tagged->title)
            ->assertDontSee($other->title)
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 1);
    }

    private function post(string $title, string $slug, array $attributes = []): Post
    {
        return Post::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'خلاصه آزمایشی خبر',
            'body' => '<p>متن کامل آزمایشی خبر</p>',
            'type' => 'news',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'meta_keywords' => null,
            'is_active' => true,
        ], $attributes));
    }
}
