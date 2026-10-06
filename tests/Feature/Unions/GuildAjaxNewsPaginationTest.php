<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class GuildAjaxNewsPaginationTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_six_union_news_per_page_with_latest_featured_and_other_unions_excluded(): void
    {
        $union = $this->union(['slug' => 'guild-news-six']);
        $otherUnion = $this->union(['slug' => 'guild-news-other']);

        for ($index = 0; $index < 9; $index++) {
            $this->publishedPost([
                'union_id' => $union->id,
                'slug' => 'guild-news-six-'.$index,
                'title' => 'خبر آزمایشی '.$index,
                'published_at' => now()->subMinutes($index + 1),
            ]);
        }

        $external = $this->publishedPost([
            'union_id' => $otherUnion->id,
            'slug' => 'guild-news-external',
            'title' => 'خبر غیرمرتبط',
            'published_at' => now(),
        ]);

        // An unrelated legacy selection must not affect automatic news.
        $union->selectedPosts()->attach($external->id, ['sort_order' => 10]);

        $response = $this->get(route('guilds.show', $union->slug));
        $response->assertOk()
            ->assertSeeInOrder(['خبر آزمایشی 0', 'خبر آزمایشی 1', 'خبر آزمایشی 5'])
            ->assertSee('guild-profile-news-feature', false)
            ->assertDontSee('خبر آزمایشی 6')
            ->assertDontSee('خبر غیرمرتبط')
            ->assertSee('news_page=2');

        $this->assertSame(5, substr_count($response->getContent(), 'class="latest-news-card"'));
    }

    public function test_manual_mode_shows_only_selected_own_news_in_admin_order_with_ajax_pagination(): void
    {
        $union = $this->union(['slug' => 'guild-news-manual', 'news_mode' => 'manual']);
        $otherUnion = $this->union(['slug' => 'guild-news-manual-other']);
        $posts = [];

        for ($index = 0; $index < 8; $index++) {
            $posts[] = $this->publishedPost([
                'union_id' => $union->id,
                'slug' => 'manual-article-'.$index,
                'title' => 'خبر دستی شماره '.$index,
                'published_at' => now()->subMinutes($index + 1),
            ]);
        }

        $foreign = $this->publishedPost([
            'union_id' => $otherUnion->id,
            'slug' => 'manual-foreign-article',
            'title' => 'خبر دستی غیرمرتبط',
        ]);
        $union->selectedPosts()->attach($foreign->id, ['sort_order' => 1]);

        foreach (array_reverse($posts) as $index => $post) {
            $union->selectedPosts()->attach($post->id, ['sort_order' => ($index + 1) * 10]);
        }

        $first = $this->get(route('guilds.show', $union->slug));
        $first->assertOk()
            ->assertSeeInOrder(['خبر دستی شماره 7', 'خبر دستی شماره 6', 'خبر دستی شماره 2'])
            ->assertDontSee('خبر دستی شماره 1')
            ->assertDontSee('خبر دستی غیرمرتبط')
            ->assertSee('news_page=2');

        $this->assertSame($posts[7]->id, $union->fresh()->latest_published_news?->id);

        $second = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('guilds.show', ['union' => $union->slug, 'news_page' => 2]));

        $second->assertOk()->assertJsonPath('total', 8)->assertJsonPath('current_page', 2);
        $html = $second->json('html');
        $this->assertStringContainsString('خبر دستی شماره 1', $html);
        $this->assertStringContainsString('خبر دستی شماره 0', $html);
        $this->assertStringNotContainsString('خبر دستی شماره 7', $html);
        $this->assertStringNotContainsString('خبر دستی غیرمرتبط', $html);
    }

    public function test_ajax_returns_only_news_partial_and_correct_second_page(): void
    {
        $union = $this->union(['slug' => 'guild-news-ajax']);
        for ($index = 0; $index < 8; $index++) {
            $this->publishedPost([
                'union_id' => $union->id,
                'slug' => 'guild-ajax-'.$index,
                'title' => 'خبر صفحه‌بندی '.$index,
                'published_at' => now()->subMinutes($index + 1),
            ]);
        }

        // Full-page links work without JS; then test the AJAX-only partial.
        $fullPage = $this->get(route('guilds.show', ['union' => $union->slug, 'news_page' => 2]));
        $fullPage->assertOk()
            ->assertSee('id="guild-news"', false)
            ->assertSee('خبر صفحه‌بندی 6')
            ->assertDontSee('خبر صفحه‌بندی 0');

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('guilds.show', ['union' => $union->slug, 'news_page' => 2]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 8);
        $html = $response->json('html');
        $this->assertStringContainsString('خبر صفحه‌بندی 6', $html);
        $this->assertStringContainsString('خبر صفحه‌بندی 7', $html);
        $this->assertStringNotContainsString('خبر صفحه‌بندی 0', $html);
        $this->assertStringNotContainsString('<html', $html);
    }

    public function test_pager_displays_at_most_six_page_numbers_plus_previous_and_next(): void
    {
        $union = $this->union(['slug' => 'guild-news-many-pages']);
        for ($index = 0; $index < 44; $index++) {
            $this->publishedPost([
                'union_id' => $union->id,
                'slug' => 'guild-many-news-'.$index,
                'published_at' => now()->subMinutes($index + 1),
            ]);
        }

        $response = $this->get(route('guilds.show', ['union' => $union->slug, 'news_page' => 4]));
        $response->assertOk()->assertSee('aria-current="page"', false);

        preg_match('/<nav class="guild-profile-news-pagination".*?<\/nav>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $numbers = substr_count($matches[0], 'aria-label="صفحه ') + substr_count($matches[0], 'aria-current="page"');
        $this->assertSame(6, $numbers);
    }

    public function test_explicit_news_disable_and_legacy_flag_never_expose_union_news(): void
    {
        $union = $this->union([
            'slug' => 'guild-news-disabled',
            'settings' => ['show_news' => false, 'show_news_slider' => true],
        ]);
        $this->publishedPost(['union_id' => $union->id, 'title' => 'خبر مخفی‌شده']);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertDontSee('id="guild-news"', false);

        $union->update(['news_mode' => 'disabled', 'settings' => ['show_news' => true]]);
        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertDontSee('id="guild-news"', false);

        $jsonResponse = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('guilds.show', $union->slug));
        $jsonResponse->assertOk()->assertJsonPath('total', 0);
        $this->assertStringNotContainsString('خبر مخفی‌شده', $jsonResponse->json('html'));
    }

    public function test_executive_without_photo_renders_as_text_without_placeholder_portrait(): void
    {
        $union = $this->union([
            'slug' => 'guild-minimal-executive',
            'manager_name' => 'رئیس',
            'executive_name' => 'مدیر اجرایی نمونه',
            'executive_image' => null,
        ]);

        $response = $this->get(route('guilds.show', $union->slug));
        $response->assertOk()
            ->assertSee('مدیر اجرایی نمونه')
            ->assertSee('guild-profile-hero__leaders--minimal', false);

        $this->assertSame(1, substr_count($response->getContent(), 'class="guild-profile-person__portrait"'));
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه نمونه',
            'title' => 'اتحادیه نمونه',
            'slug' => 'guild-news-'.uniqid(),
            'news_enabled' => true,
            'news_mode' => 'auto',
            'is_active' => true,
        ], $overrides));
    }
}
