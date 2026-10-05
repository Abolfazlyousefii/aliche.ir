<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\CommissionTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionDirectoryUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_renders_dynamic_images_fallbacks_and_published_counts(): void
    {
        $withImage = Commission::query()->create([
            'title' => 'کمیسیون بازرسی',
            'slug' => 'inspection-commission-ui',
            'description' => '<p>شرح کمیسیون بازرسی</p>',
            'image' => 'commissions/inspection.jpg',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        CommissionTask::query()->create([
            'commission_id' => $withImage->id,
            'title' => 'وظیفه فعال',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CommissionTask::query()->create([
            'commission_id' => $withImage->id,
            'title' => 'وظیفه غیرفعال',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        CommissionSession::query()->create([
            'commission_id' => $withImage->id,
            'title' => 'جلسه منتشرشده',
            'session_date' => now()->subDay(),
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CommissionSession::query()->create([
            'commission_id' => $withImage->id,
            'title' => 'جلسه پیش‌نویس',
            'session_date' => now(),
            'status' => 'draft',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Commission::query()->create([
            'title' => 'کمیسیون بدون تصویر',
            'slug' => 'fallback-commission-ui',
            'description' => null,
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->get(route('commissions.index'))
            ->assertOk()
            ->assertSee('commissions-directory-v2', false)
            ->assertSee('commission-directory-card__media has-image', false)
            ->assertSee('commission-directory-card__media is-fallback', false)
            ->assertSee($withImage->image_url, false)
            ->assertSee('کمیسیون بازرسی')
            ->assertSee('کمیسیون بدون تصویر')
            ->assertSee('۱')
            ->assertSee('وظیفه')
            ->assertSee('جلسه منتشرشده');
    }

    public function test_directory_hides_unpublished_and_inactive_commissions(): void
    {
        Commission::query()->create([
            'title' => 'کمیسیون قابل نمایش',
            'slug' => 'visible-commission-ui',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        Commission::query()->create([
            'title' => 'کمیسیون پیش‌نویس',
            'slug' => 'draft-commission-ui',
            'status' => 'draft',
            'is_active' => true,
        ]);

        Commission::query()->create([
            'title' => 'کمیسیون غیرفعال',
            'slug' => 'inactive-commission-ui',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'is_active' => false,
        ]);

        $this->get(route('commissions.index'))
            ->assertOk()
            ->assertSee('کمیسیون قابل نمایش')
            ->assertDontSee('کمیسیون پیش‌نویس')
            ->assertDontSee('کمیسیون غیرفعال');
    }
}
