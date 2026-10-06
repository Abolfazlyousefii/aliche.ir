<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class GuildProfileV4Test extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_hero_displays_independent_leaders_without_a_sidebar(): void
    {
        $union = $this->union([
            'manager_name' => 'رئیس آزمایشی',
            'manager_position' => 'رئیس اتحادیه',
            'executive_name' => 'مدیر اجرایی آزمایشی',
            'executive_position' => 'مدیر اجرایی',
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('guild-profile-page--v4', false)
            ->assertSee('رئیس آزمایشی')
            ->assertSee('مدیر اجرایی آزمایشی')
            ->assertSee('guild-profile-person--president', false)
            ->assertSee('guild-profile-person--executive', false)
            ->assertSee('id="guild-manager"', false)
            ->assertDontSee('guild-profile-sidebar', false);
    }

    public function test_executive_card_is_not_invented_for_legacy_unions(): void
    {
        $union = $this->union([
            'manager_name' => 'رئیس قدیمی اتحادیه',
            'executive_name' => null,
            'executive_image' => null,
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('رئیس قدیمی اتحادیه')
            ->assertDontSee('guild-profile-person--executive', false);
    }

    public function test_latest_related_news_is_featured_and_exactly_six_more_are_shown(): void
    {
        $union = $this->union([
            'slug' => 'guild-seven-news',
            'news_enabled' => true,
            'settings' => ['show_news' => true],
        ]);

        for ($index = 0; $index < 9; $index++) {
            $this->publishedPost([
                'slug' => 'guild-seven-item-'.$index,
                'title' => 'خبر مرتبط شماره '.$index,
                'type' => 'news',
                'union_id' => $union->id,
                'published_at' => now()->subMinutes($index + 1),
            ]);
        }

        $unrelatedUnion = $this->union(['slug' => 'different-guild']);
        $this->publishedPost([
            'slug' => 'different-guild-story',
            'title' => 'خبر اتحادیه دیگر',
            'union_id' => $unrelatedUnion->id,
            'published_at' => now(),
        ]);

        $response = $this->get(route('guilds.show', $union->slug));

        $response->assertOk()
            ->assertSee('guild-profile-news-feature', false)
            ->assertSee('guild-profile-news-grid', false)
            ->assertSeeInOrder([
                'خبر مرتبط شماره 0',
                'خبر مرتبط شماره 1',
                'خبر مرتبط شماره 6',
            ])
            ->assertDontSee('خبر مرتبط شماره 7')
            ->assertDontSee('خبر مرتبط شماره 8')
            ->assertDontSee('خبر اتحادیه دیگر');

        $this->assertSame(6, substr_count($response->getContent(), 'class="guild-profile-news-card"'));
    }

    public function test_news_precedes_commissions_and_other_sections(): void
    {
        $union = $this->union([
            'manager_name' => 'رئیس',
            'news_enabled' => true,
            'settings' => ['show_news' => true, 'show_manager' => true],
        ]);
        $this->publishedPost(['slug' => 'news-before-services', 'union_id' => $union->id]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSeeInOrder([
                'id="guild-manager"',
                'id="guild-news"',
            ], false)
            ->assertDontSee('guild-profile-sidebar', false);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه آزمایشی',
            'title' => 'اتحادیه آزمایشی',
            'slug' => 'guild-v4-'.uniqid(),
            'news_enabled' => true,
            'is_active' => true,
        ], $overrides));
    }
}
