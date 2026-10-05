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

    public function test_related_editorial_content_keeps_the_guild_news_row_contract(): void
    {
        $union = $this->union([
            'slug' => 'guild-with-news',
            'news_enabled' => true,
            'settings' => ['show_news' => true],
        ]);
        $post = $this->publishedPost([
            'title' => 'گزارش مرتبط اتحادیه تست',
            'slug' => 'guild-related-report',
            'type' => 'report',
            'union_id' => $union->id,
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('id="guild-news"', false)
            ->assertSee('guild-profile-news-list', false)
            ->assertSee($post->title);
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
            'settings' => ['show_news' => true],
        ]);
        $this->publishedPost([
            'slug' => 'services-enabled-news',
            'union_id' => $enabled->id,
        ]);

        $this->get(route('guilds.show', $enabled->slug))
            ->assertOk()
            ->assertSee('id="guild-services"', false);
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
