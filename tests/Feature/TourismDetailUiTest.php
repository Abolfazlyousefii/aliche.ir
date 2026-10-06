<?php

namespace Tests\Feature;

use App\Models\TourismPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourismDetailUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_detail_page_uses_compact_v2_layout_with_real_visit_data(): void
    {
        Storage::disk('public')->put('tourism/hero.jpg', 'image');
        Storage::disk('public')->put('tourism/gallery-one.jpg', 'image');

        $place = $this->place([
            'title' => 'پارک جنگلی آزمایشی',
            'slug' => 'tourism-detail-v2',
            'description' => '<h2>معرفی مقصد</h2><p>توضیحات واقعی مقصد.</p>',
            'image' => 'tourism/hero.jpg',
            'gallery' => [
                ['path' => 'tourism/gallery-one.jpg', 'caption' => 'نمای جنگل'],
            ],
            'location' => 'گرگان',
            'address' => 'گرگان، مسیر جنگلی',
            'phone' => '01712345678',
            'working_hours' => 'همه روزه',
            'visit_price' => 'رایگان',
            'latitude' => '36.8000000',
            'longitude' => '54.4000000',
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('tourism-detail-v2', false)
            ->assertSee('tourism-detail-v2__hero', false)
            ->assertSee('tourism-detail-v2__rich-text', false)
            ->assertSee('tourism-detail-v2__facts', false)
            ->assertSee('tourism-detail-v2__gallery-grid', false)
            ->assertSee('مسیریابی')
            ->assertSee('تماس')
            ->assertSee('همه روزه')
            ->assertSee('رایگان')
            ->assertDontSee('py-5 bg-white border-bottom', false);
    }

    public function test_missing_optional_visit_data_is_not_rendered_as_fake_placeholder_cards(): void
    {
        $place = $this->place([
            'title' => 'مقصد بدون اطلاعات تکمیلی',
            'slug' => 'tourism-detail-no-fake-data',
            'description' => '<p>فقط معرفی مقصد.</p>',
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('tourism-detail-v2', false)
            ->assertDontSee('آدرس این مکان هنوز ثبت نشده است.')
            ->assertDontSee('ساعت بازدید هنوز ثبت نشده است.')
            ->assertDontSee('هزینه بازدید هنوز ثبت نشده است.')
            ->assertDontSee('شماره تماس ثبت نشده است.')
            ->assertDontSee('tourism-detail-v2__facts', false);
    }

    public function test_detail_renders_long_address_safely_without_a_location(): void
    {
        $place = $this->place([
            'location' => null,
            'address' => str_repeat('آدرس گردشگری ', 8),
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('tourism-detail-v2__hero-meta', false)
            ->assertSee('آدرس گردشگری');
    }

    public function test_detail_sanitizes_legacy_rich_text_on_output(): void
    {
        $place = $this->place([
            'description' => '<p onclick="alert(1)">محتوای گردشگری</p><script>window.unsafe=true</script>',
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('محتوای گردشگری')
            ->assertDontSee('onclick=', false)
            ->assertDontSee('window.unsafe', false);
    }

    private function place(array $overrides = []): TourismPlace
    {
        return TourismPlace::query()->create(array_replace([
            'title' => 'مکان گردشگری آزمایشی',
            'slug' => 'tourism-place-'.uniqid(),
            'short_description' => 'توضیح کوتاه مقصد گردشگری.',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides));
    }
}
