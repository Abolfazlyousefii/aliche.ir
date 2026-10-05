<?php

namespace Tests\Feature;

use App\Models\TourismPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeVisibleContentRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_does_not_invent_demo_cards_when_real_content_is_missing(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('خدمت الکترونیکی فعالی برای نمایش ثبت نشده است.')
            ->assertSee('سامانه فعالی برای نمایش در صفحه نخست ثبت نشده است.')
            ->assertSee('کمیسیون فعالی برای نمایش در صفحه نخست ثبت نشده است.')
            ->assertSee('ویدیوی منتشرشده‌ای برای نمایش وجود ندارد.')
            ->assertSee('گالری منتشرشده‌ای برای نمایش وجود ندارد.')
            ->assertDontSee('سامانه آموزش اصناف')
            ->assertDontSee('گزارش تصویری از خدمات اتاق اصناف مرکز استان گلستان به کسبه شهرستان')
            ->assertDontSee('نمایشگاه صنایع دستی و سوغات استان گلستان');
    }

    public function test_home_marks_tourism_records_without_real_image_instead_of_showing_generic_photo(): void
    {
        TourismPlace::query()->create([
            'title' => 'جاذبه بدون تصویر',
            'slug' => 'tourism-without-real-image',
            'short_description' => 'رکورد تست برای نمایش وضعیت تصویر',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('جاذبه بدون تصویر')
            ->assertSee('تصویر ثبت نشده')
            ->assertSee('tourism-img-wrap is-missing-image', false);
    }
}
