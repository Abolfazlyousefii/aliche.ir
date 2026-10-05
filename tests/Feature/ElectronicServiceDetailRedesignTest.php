<?php

namespace Tests\Feature;

use App\Models\ElectronicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectronicServiceDetailRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_detail_uses_minimal_v2_layout_and_real_public_link(): void
    {
        $service = ElectronicService::query()->create([
            'title' => 'صدور پروانه کسب',
            'slug' => 'issue-business-license',
            'short_description' => 'راهنمای استفاده از خدمت صدور پروانه کسب',
            'body' => '<h2>مدارک موردنیاز</h2><p>اطلاعات واقعی خدمت از پنل مدیریت.</p>',
            'icon' => '📝',
            'link_type' => 'external',
            'link' => 'https://service.test/license',
            'target' => '_blank',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ElectronicService::query()->create([
            'title' => 'تمدید پروانه کسب',
            'slug' => 'renew-business-license',
            'short_description' => 'خدمت مرتبط',
            'link_type' => 'none',
            'target' => '_self',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->get(route('electronic-services.show', $service->slug))
            ->assertOk()
            ->assertSee('service-detail-v2', false)
            ->assertSee('service-detail-v2__hero', false)
            ->assertSee('service-detail-v2__content-card', false)
            ->assertSee('service-detail-v2__related', false)
            ->assertSee('https://service.test/license', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('ورود به سامانه صدور پروانه کسب')
            ->assertSee('اطلاعات خدمت')
            ->assertSee('مدارک موردنیاز')
            ->assertSee('تمدید پروانه کسب')
            ->assertDontSee('portal-detail-cover', false);
    }

    public function test_service_without_public_link_stays_in_guide_mode_without_fake_cta(): void
    {
        $service = ElectronicService::query()->create([
            'title' => 'راهنمای خدمت بدون لینک',
            'slug' => 'guide-only-service',
            'short_description' => 'این خدمت فعلاً راهنمای اطلاعاتی است.',
            'body' => '<p>متن راهنما</p>',
            'link_type' => 'none',
            'target' => '_self',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('electronic-services.show', $service->slug))
            ->assertOk()
            ->assertSee('راهنمای اطلاعاتی')
            ->assertDontSee('service-detail-v2__primary-action', false)
            ->assertDontSee('برای شروع آماده‌اید؟');
    }
}
