<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDetailUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_system_uses_dynamic_v2_layout_with_real_data(): void
    {
        $category = Category::query()->create([
            'title' => 'سامانه‌های خدماتی',
            'slug' => 'system-services-detail',
            'type' => 'system',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $system = $this->system('سامانه نوین آزمایشی', 'modern-system-detail', [
            'category_id' => $category->id,
            'short_description' => 'دسترسی سریع به خدمات صنفی',
            'description' => '<h2>راهنمای استفاده</h2><p>توضیحات واقعی ثبت‌شده از پنل مدیریت.</p>',
            'image' => 'systems/modern.jpg',
            'link' => 'https://service.test/modern',
            'target' => '_blank',
        ]);

        $related = $this->system('سامانه مرتبط', 'related-system-detail', [
            'category_id' => $category->id,
            'sort_order' => 2,
        ]);

        $this->get(route('systems.show', $system->slug))
            ->assertOk()
            ->assertSee('system-detail-v2', false)
            ->assertSee('system-detail-v2__hero', false)
            ->assertSee('system-detail-v2__related', false)
            ->assertSee('سامانه نوین آزمایشی')
            ->assertSee('سامانه‌های خدماتی')
            ->assertSee('systems/modern.jpg', false)
            ->assertSee('https://service.test/modern', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('راهنمای استفاده')
            ->assertSee($related->title)
            ->assertDontSee('portal-detail-layout', false);
    }

    public function test_system_without_valid_link_has_information_state_and_no_primary_cta(): void
    {
        $system = $this->system('سامانه اطلاعاتی', 'information-only-system', [
            'link' => 'javascript:alert(1)',
            'description' => '<p>راهنمای سامانه اطلاعاتی.</p>',
        ]);

        $this->get(route('systems.show', $system->slug))
            ->assertOk()
            ->assertSee('اطلاعات سامانه')
            ->assertSee('لینک ورود معتبر برای این سامانه ثبت نشده است')
            ->assertDontSee('system-detail-v2__primary-action', false)
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_legacy_invalid_category_is_not_presented_as_system_category(): void
    {
        $videoCategory = Category::query()->create([
            'title' => 'ویدیوهای عمومی',
            'slug' => 'legacy-video-category',
            'type' => 'video',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $system = $this->system('سامانه دسته قدیمی', 'legacy-category-system', [
            'category_id' => $videoCategory->id,
        ]);

        $this->get(route('systems.show', $system->slug))
            ->assertOk()
            ->assertSee('سامانه صنفی')
            ->assertDontSee('ویدیوهای عمومی');
    }

    private function system(string $title, string $slug, array $attributes = []): System
    {
        return System::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'توضیح کوتاه سامانه',
            'description' => '<p>توضیحات سامانه</p>',
            'icon' => '💻',
            'link' => null,
            'target' => '_self',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }
}
