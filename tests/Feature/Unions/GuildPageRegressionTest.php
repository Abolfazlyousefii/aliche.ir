<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use App\Models\UnionMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class GuildPageRegressionTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_related_editorial_content_uses_featured_news_and_homepage_style_rows(): void
    {
        $union = $this->union([
            'slug' => 'guild-with-news',
            'news_enabled' => true,
            'settings' => ['show_news' => true],
        ]);
        $olderPost = $this->publishedPost([
            'title' => 'گزارش دوم اتحادیه تست',
            'slug' => 'guild-related-report-older',
            'type' => 'report',
            'union_id' => $union->id,
            'published_at' => now()->subDay(),
        ]);
        $latestPost = $this->publishedPost([
            'title' => 'گزارش اصلی اتحادیه تست',
            'slug' => 'guild-related-report-latest',
            'type' => 'report',
            'union_id' => $union->id,
            'published_at' => now(),
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('id="guild-news"', false)
            ->assertSee('guild-profile-news-feature', false)
            ->assertSee('guild-profile-news-rows', false)
            ->assertSee($latestPost->title)
            ->assertSee($olderPost->title)
            ->assertDontSee('guild-profile-hero-news', false);
    }

    public function test_seed_placeholder_members_are_hidden_but_real_board_members_remain_visible(): void
    {
        $union = $this->union([
            'slug' => 'guild-board-filter',
            'members_enabled' => true,
            'settings' => ['show_board_members' => true],
        ]);

        UnionMember::query()->create([
            'union_id' => $union->id,
            'full_name' => 'عضو صنفی 1 '.$union->name,
            'membership_code' => 'G'.$union->id.'-1',
            'business_name' => 'واحد صنفی 1 '.$union->name,
            'description' => 'عضو فعال برای نمایش در صفحه اتحادیه',
            'position' => null,
            'image' => null,
            'status' => 'active',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        UnionMember::query()->create([
            'union_id' => $union->id,
            'full_name' => 'عضو واقعی هیئت‌مدیره',
            'position' => 'نایب رئیس',
            'status' => 'active',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertDontSee('عضو صنفی 1 '.$union->name)
            ->assertSee('عضو واقعی هیئت‌مدیره')
            ->assertSee('نایب رئیس');
    }

    public function test_president_actions_and_social_links_only_render_supported_safe_urls(): void
    {
        $union = $this->union([
            'slug' => 'safe-union-links',
            'manager_name' => 'مدیر تست',
            'website' => 'javascript:alert(2)',
            'social_links' => [
                'instagram' => 'https://instagram.com/example',
                'unsupported' => 'https://example.com/unsupported',
            ],
            'president_buttons' => [
                [
                    'title' => 'لینک ناامن',
                    'url' => 'javascript:alert(1)',
                    'icon' => 'phone',
                    'target' => '_blank',
                    'is_active' => true,
                ],
                [
                    'title' => 'تماس امن',
                    'url' => 'tel:01712345678',
                    'icon' => 'phone',
                    'target' => '_self',
                    'is_active' => true,
                ],
            ],
            'settings' => [
                'show_manager' => true,
                'show_contact' => true,
                'show_social_links' => true,
            ],
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('تماس امن')
            ->assertSee('https://instagram.com/example', false)
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('javascript:alert(2)', false)
            ->assertDontSee('لینک ناامن')
            ->assertDontSee('https://example.com/unsupported', false);
    }

    public function test_services_section_respects_services_enabled_and_requires_displayable_content(): void
    {
        $disabled = $this->union([
            'slug' => 'services-disabled-union',
            'news_enabled' => true,
            'services_enabled' => false,
            'settings' => ['show_news' => true],
        ]);
        $this->publishedPost([
            'slug' => 'services-disabled-news',
            'union_id' => $disabled->id,
        ]);

        $this->get(route('guilds.show', $disabled->slug))
            ->assertOk()
            ->assertDontSee('id="guild-services"', false);

        $enabled = $this->union([
            'slug' => 'services-enabled-union',
            'news_enabled' => true,
            'services_enabled' => true,
            'manager_name' => 'رئیس اتحادیه تست',
            'settings' => [
                'show_manager' => true,
                'show_news' => true,
            ],
        ]);
        $this->publishedPost([
            'slug' => 'services-enabled-news',
            'union_id' => $enabled->id,
        ]);

        $this->get(route('guilds.show', $enabled->slug))
            ->assertOk()
            ->assertSee('id="guild-services"', false)
            ->assertSeeInOrder([
                'id="guild-manager"',
                'id="guild-news"',
                'id="guild-services"',
            ], false);
    }

    public function test_full_rich_union_introduction_and_president_bio_render_safely_with_zero_price(): void
    {
        $union = $this->union([
            'slug' => 'guild-full-introduction',
            'manager_name' => 'رئیس نمونه اتحادیه',
            'manager_description' => 'معرفی کامل و بدون کوتاه‌شدن رئیس اتحادیه برای اعضای صنفی',
            'description' => '<p><strong>توضیحات کامل و اختصاصی اتحادیه</strong></p><script>alert("unsafe")</script>',
            'price_list_mode' => 'table',
            'settings' => ['show_manager' => true, 'show_prices' => true],
        ]);

        $union->prices()->create([
            'title' => 'خدمات بدون هزینه',
            'price' => 0,
            'currency' => 'ریال',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('id="guild-about"', false)
            ->assertSee('<strong>توضیحات کامل و اختصاصی اتحادیه</strong>', false)
            ->assertSee('معرفی کامل و بدون کوتاه‌شدن رئیس اتحادیه برای اعضای صنفی')
            ->assertSee('خدمات بدون هزینه')
            ->assertSee('۰ ریال')
            ->assertDontSee('alert("unsafe")', false)
            ->assertDontSee('<script>', false);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه صفحه تکی',
            'title' => 'اتحادیه صفحه تکی',
            'slug' => 'guild-page-'.uniqid(),
            'news_enabled' => true,
            'services_enabled' => false,
            'is_active' => true,
        ], $overrides));
    }
}
