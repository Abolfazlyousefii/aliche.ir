<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use App\Models\UnionCommission;
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

    public function test_latest_related_news_is_featured_and_only_five_more_are_shown(): void
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
            ->assertSee('guild-profile-news-rows', false)
            ->assertSeeInOrder([
                'خبر مرتبط شماره 0',
                'خبر مرتبط شماره 1',
                'خبر مرتبط شماره 5',
            ])
            ->assertDontSee('خبر مرتبط شماره 6')
            ->assertDontSee('خبر مرتبط شماره 7')
            ->assertDontSee('خبر مرتبط شماره 8')
            ->assertDontSee('خبر اتحادیه دیگر');

        $this->assertSame(5, substr_count($response->getContent(), 'class="latest-news-card"'));
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

    public function test_about_union_moves_above_news_and_commissions_without_changing_its_content(): void
    {
        $union = $this->union([
            'slug' => 'guild-about-before-news',
            'description' => '<p><strong>معرفی رسمی اتحادیه</strong></p>',
            'manager_name' => 'رئیس اتحادیه',
            'manager_description' => 'سوابق و شرح مسئولیت رئیس اتحادیه',
            'news_enabled' => true,
            'settings' => [
                'show_news' => true,
                'show_commissions' => true,
                'show_manager' => true,
            ],
        ]);
        $this->publishedPost([
            'slug' => 'guild-about-order-news',
            'union_id' => $union->id,
        ]);
        UnionCommission::query()->create([
            'union_id' => $union->id,
            'title' => 'کمیسیون فعال اتحادیه',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('guilds.show', $union->slug));

        $response->assertOk()
            ->assertSeeInOrder([
                'guild-profile-quickbar--sections',
                'id="guild-about"',
                'id="guild-news"',
                'id="guild-commissions"',
            ], false)
            ->assertSee('<strong>معرفی رسمی اتحادیه</strong>', false)
            ->assertSee('معرفی رئیس اتحادیه: رئیس اتحادیه')
            ->assertSee('سوابق و شرح مسئولیت رئیس اتحادیه');

        $this->assertSame(1, substr_count($response->getContent(), 'id="guild-about"'));
    }

    public function test_about_union_is_not_added_when_no_about_information_exists(): void
    {
        $union = $this->union([
            'slug' => 'guild-without-about-content',
            'description' => null,
            'manager_description' => null,
            'news_enabled' => true,
            'settings' => ['show_news' => true],
        ]);
        $this->publishedPost(['slug' => 'guild-no-about-news', 'union_id' => $union->id]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertDontSee('id="guild-about"', false)
            ->assertSee('id="guild-news"', false);
    }

    public function test_quick_links_are_news_commissions_services_and_contact_in_requested_order(): void
    {
        $union = $this->union([
            'slug' => 'guild-four-quick-links',
            'news_enabled' => true,
            'services_enabled' => true,
            'phone' => '01732345678',
            'settings' => ['show_news' => true, 'show_commissions' => true, 'show_contact' => true],
        ]);
        $this->publishedPost(['slug' => 'guild-quicklink-news', 'union_id' => $union->id]);

        UnionCommission::query()->create([
            'union_id' => $union->id,
            'title' => 'کمیسیون تخصصی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('guilds.show', $union->slug));
        $response->assertOk()
            ->assertSee('guild-profile-quickbar--sections', false)
            ->assertSee('id="guild-news"', false)
            ->assertSee('id="guild-commissions"', false)
            ->assertDontSee('id="guild-services"', false)
            ->assertDontSee('خدمات و محتوای اتحادیه')
            ->assertDontSee('class="guild-profile-services"', false)
            ->assertSee('id="guild-contact"', false)
            ->assertSee('href="'.route('electronic-services.index').'" class="guild-profile-quickbar__item"', false);

        preg_match_all(
            '/<a href="([^"]+)" class="guild-profile-quickbar__item">/',
            $response->getContent(),
            $links
        );
        $this->assertSame(
            ['#guild-news', '#guild-commissions', route('electronic-services.index'), '#guild-contact'],
            $links[1]
        );
        $this->assertStringContainsString('--guild-quickbar-count: 4', $response->getContent());
    }

    public function test_quick_links_do_not_point_to_hidden_or_empty_sections(): void
    {
        $union = $this->union([
            'slug' => 'guild-only-contact-quicklink',
            'manager_name' => 'رئیس اتحادیه',
            'services_enabled' => true,
            'phone' => '01732345678',
            'settings' => ['show_news' => true, 'show_commissions' => true, 'show_contact' => true],
        ]);

        $response = $this->get(route('guilds.show', $union->slug));
        $response->assertOk()
            ->assertSee('id="guild-manager"', false)
            ->assertSee('id="guild-contact"', false);

        preg_match_all(
            '/<a href="#(guild-[a-z-]+)" class="guild-profile-quickbar__item">/',
            $response->getContent(),
            $links
        );
        $this->assertSame(['guild-contact'], $links[1]);
    }

    public function test_news_preview_uses_split_layout_only_when_more_than_one_item_exists(): void
    {
        $union = $this->union([
            'slug' => 'union-preview-editorial',
            'news_enabled' => true,
            'settings' => ['show_news' => true],
        ]);
        $this->publishedPost([
            'slug' => 'preview-lead-news', 'title' => 'خبر شاخص اتحادیه',
            'union_id' => $union->id, 'published_at' => now()->subMinutes(1),
        ]);
        $this->publishedPost([
            'slug' => 'preview-secondary-news', 'title' => 'خبر دوم اتحادیه',
            'union_id' => $union->id, 'published_at' => now()->subMinutes(2),
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('guild-profile-news-result--split', false)
            ->assertSee('guild-profile-news-feature', false)
            ->assertSee('guild-profile-news-rows', false)
            ->assertSeeInOrder(['خبر شاخص اتحادیه', 'خبر دوم اتحادیه']);

        $single = $this->union(['slug' => 'union-preview-single-news', 'news_enabled' => true]);
        $this->publishedPost(['slug' => 'preview-only-news', 'union_id' => $single->id]);
        $this->get(route('guilds.show', $single->slug))
            ->assertOk()
            ->assertDontSee('guild-profile-news-result--split', false)
            ->assertSee('guild-profile-news-feature', false);
    }

    public function test_about_preview_uses_existing_president_portrait_without_duplicate_bio(): void
    {
        $union = $this->union([
            'slug' => 'union-about-portrait-preview',
            'description' => '<p>متن معرفی اتحادیه</p>',
            'manager_name' => 'رئیس اتحادیه نمونه',
            'manager_image' => 'unions/president-portrait.jpg',
            'manager_description' => 'شرح سوابق رئیس اتحادیه',
        ]);

        $response = $this->get(route('guilds.show', $union->slug));
        $response->assertOk()
            ->assertSee('guild-profile-about__layout--with-portrait', false)
            ->assertSee('guild-profile-about__portrait', false)
            ->assertSee('unions/president-portrait.jpg')
            ->assertSee('متن معرفی اتحادیه')
            ->assertSee('شرح سوابق رئیس اتحادیه');

        $this->assertSame(1, substr_count($response->getContent(), 'id="guild-about"'));
    }

    public function test_preview_styles_are_scoped_to_union_and_both_public_trees_match(): void
    {
        $canonical = file_get_contents(base_path('public/assets/css/guild-minimal.css'));
        $deployable = file_get_contents(base_path('public_html/assets/css/guild-minimal.css'));

        $this->assertNotFalse($canonical);
        $this->assertSame($canonical, $deployable);
        foreach ([
            'guild-profile-news-result--split',
            'guild-profile-hero__board',
            'guild-profile-about__layout--with-portrait',
            'guild-profile-contact-grid',
            'guild-profile-gallery',
            'guild-profile-quickbar--sections',
            '@media (max-width: 767.98px)',
            '@media (max-width: 575.98px)',
        ] as $selector) {
            $this->assertStringContainsString($selector, $canonical);
        }
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
