<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GuildUnion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuildDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_directory_shows_all_active_unions_and_excludes_inactive_items(): void
    {
        $this->union('اتحادیه تولیدی فعال', 'active-production', ['union_type' => 'production', 'sort_order' => 20]);
        $this->union('اتحادیه خدماتی فعال', 'active-service', ['union_type' => 'service', 'sort_order' => 10]);
        $this->union('اتحادیه غیرفعال', 'inactive-union', ['is_active' => false]);

        $this->get(route('guilds.index'))
            ->assertOk()
            ->assertViewIs('frontend.guilds.index')
            ->assertSee('اتحادیه تولیدی فعال')
            ->assertSee('اتحادیه خدماتی فعال')
            ->assertDontSee('اتحادیه غیرفعال')
            ->assertSee('guilds-directory-page', false)
            ->assertSee('data-type=""', false)
            ->assertViewHas('unions', fn ($unions) => $unions->total() === 2)
            ->assertViewHas('type', '');
    }

    public function test_type_tabs_filter_the_single_grid_and_invalid_type_falls_back_to_all(): void
    {
        $this->union('عضو تولیدی', 'production-member', ['union_type' => 'production']);
        $this->union('عضو توزیعی', 'distribution-member', ['union_type' => 'distribution']);
        $this->union('عضو خدماتی', 'service-member', ['union_type' => 'service']);
        $this->union('عضو تخصصی', 'specialized-member', ['union_type' => 'specialized']);

        foreach (['production', 'distribution', 'service', 'specialized'] as $type) {
            $this->get(route('guilds.index', ['type' => $type]))
                ->assertOk()
                ->assertViewHas('unions', fn ($unions) => $unions->total() === 1)
                ->assertViewHas('type', $type);
        }

        $this->get(route('guilds.index', ['type' => 'invalid']))
            ->assertOk()
            ->assertViewHas('unions', fn ($unions) => $unions->total() === 4)
            ->assertViewHas('type', '');

        $this->get(route('guilds.index', ['type' => 'production', 'search' => 'ناموجود']))
            ->assertOk()
            ->assertSee('نتیجه‌ای مطابق عبارت جستجو پیدا نشد.');
    }

    public function test_type_with_zero_active_items_remains_clickable_and_shows_group_empty_state(): void
    {
        $this->union('عضو خدماتی', 'only-service-member', ['union_type' => 'service']);

        $this->get(route('guilds.index', ['type' => 'production']))
            ->assertOk()
            ->assertSee('در این گروه هنوز اتحادیه فعالی ثبت نشده است.')
            ->assertSee('data-type="production"', false)
            ->assertViewHas('typeCounts', fn ($counts) => $counts['production'] === 0 && $counts['service'] === 1);
    }

    public function test_search_matches_union_name_manager_phone_and_mobile(): void
    {
        $this->union('اتحادیه مکانیک گرگان', 'mechanics', [
            'manager_name' => 'علی رضایی',
            'phone' => '017-32220001',
            'mobile' => '09110000001',
        ]);
        $this->union('اتحادیه پوشاک', 'clothing', ['manager_name' => 'مدیر دیگر']);

        foreach (['مکانیک', 'علی رضایی', '32220001', '09110000001'] as $search) {
            $this->get(route('guilds.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('اتحادیه مکانیک گرگان')
                ->assertDontSee('اتحادیه پوشاک')
                ->assertViewHas('unions', fn ($unions) => $unions->total() === 1);
        }

        $this->get(route('guilds.index', ['search' => 'الف']))
            ->assertOk();
    }

    public function test_category_compatibility_missing_fields_avatar_tel_and_complaint_link_are_safe(): void
    {
        $category = Category::query()->create([
            'title' => 'دسته مستقل آزمایشی',
            'slug' => 'independent-test-category',
            'type' => 'union',
            'is_active' => true,
        ]);
        $this->union('اتحادیه بدون مشخصات', 'minimal-union', [
            'category_id' => $category->id,
            'manager_name' => null,
            'phone' => null,
            'mobile' => null,
            'logo' => null,
            'cover_image' => null,
        ]);
        $complaintUnion = $this->union('اتحادیه قابل شکایت', 'complaint-union', [
            'category_id' => $category->id,
            'phone' => '۰۱۷ ۳۲۲۲-۰۰۰۲',
            'complaint_enabled' => true,
        ]);

        $response = $this->get(route('guilds.index', ['category_id' => $category->id]));
        $response->assertOk()
            ->assertSee('اتحادیه بدون مشخصات')
            ->assertSee('guild-directory-logo-fallback', false)
            ->assertDontSee('asnaf-gorgan-default.jpg', false)
            ->assertSee('tel:01732220002', false)
            ->assertSee(route('complaints.create', ['union' => $complaintUnion->id]), false)
            ->assertViewHas('unions', fn ($unions) => $unions->total() === 2);

        $this->get(route('guilds.index', ['category_id' => 'invalid']))
            ->assertOk()
            ->assertViewHas('unions', fn ($unions) => $unions->total() === 2);
    }

    public function test_ajax_pagination_returns_shared_results_counts_and_preserves_query_parameters(): void
    {
        foreach (range(1, 13) as $index) {
            $this->union("اتحادیه خدماتی {$index}", "service-union-{$index}", [
                'union_type' => 'service',
                'manager_name' => 'مدیر خدمات',
                'sort_order' => $index,
            ]);
        }
        $this->union('اتحادیه غیرفعال ایجکس', 'inactive-ajax', ['is_active' => false, 'union_type' => 'service']);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('guilds.index', [
            'type' => 'service',
            'search' => 'مدیر خدمات',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 13)
            ->assertJsonPath('from', 13)
            ->assertJsonPath('to', 13)
            ->assertJsonPath('type_counts.all', 13)
            ->assertJsonPath('type_counts.service', 13)
            ->assertJsonStructure(['html', 'current_page', 'last_page', 'total', 'from', 'to', 'type_counts', 'url']);

        $html = $response->json('html');
        $this->assertStringContainsString('data-guilds-results', $html);
        $this->assertStringContainsString('guilds-directory-pagination', $html);
        $this->assertStringContainsString('type=service', $html);
        $this->assertStringContainsString('search=%D9%85%D8%AF%DB%8C%D8%B1%20%D8%AE%D8%AF%D9%85%D8%A7%D8%AA', $html);
        $this->assertStringNotContainsString('اتحادیه غیرفعال ایجکس', $html);
    }

    public function test_empty_directory_uses_professional_empty_state(): void
    {
        $this->get(route('guilds.index'))
            ->assertOk()
            ->assertSee('اتحادیه‌ای پیدا نشد')
            ->assertSee('نمایش همه اتحادیه‌ها')
            ->assertSee(route('guilds.index'), false);
    }

    private function union(string $title, string $slug, array $attributes = []): GuildUnion
    {
        return GuildUnion::query()->create(array_merge([
            'name' => $title,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'توضیح کوتاه اتحادیه برای آزمون دایرکتوری.',
            'is_active' => true,
            'sort_order' => 0,
            'complaint_enabled' => false,
        ], $attributes));
    }
}
