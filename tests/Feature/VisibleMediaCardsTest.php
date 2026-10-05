<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\ElectronicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisibleMediaCardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_electronic_service_index_renders_registered_service_image(): void
    {
        $service = ElectronicService::query()->create([
            'title' => 'صدور پروانه کسب',
            'slug' => 'issue-license-visible-image',
            'short_description' => 'خدمت آزمایشی',
            'image' => 'electronic-services/license.jpg',
            'link_type' => 'none',
            'target' => '_self',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('electronic-services.index'))
            ->assertOk()
            ->assertSee('has-media', false)
            ->assertSee($service->image_url, false)
            ->assertSee('صدور پروانه کسب');
    }

    public function test_commission_index_prefers_registered_image_over_numeric_fallback(): void
    {
        $commission = Commission::query()->create([
            'title' => 'کمیسیون آزمون',
            'slug' => 'commission-visible-image',
            'description' => 'توضیحات کمیسیون',
            'image' => 'commissions/cover.jpg',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('commissions.index'))
            ->assertOk()
            ->assertSee('commission-directory-card__media has-image', false)
            ->assertSee($commission->image_url, false)
            ->assertSee('کمیسیون آزمون');
    }
}
