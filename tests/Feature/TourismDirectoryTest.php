<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\TourismPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourismDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_directory_shows_only_active_published_places_with_real_type_counts(): void
    {
        $this->place('جاذبه طبیعی', 'nature-place', 'nature', ['sort_order' => 1]);
        $this->place('جاذبه تاریخی', 'historic-place', 'historic', ['sort_order' => 2]);
        $this->place('بازار', 'shopping-place', 'shopping', ['sort_order' => 3]);
        $this->place('نوع ناسازگار', 'other-place', 'legacy', ['sort_order' => 4]);
        $this->place('غیرفعال', 'inactive-place', 'nature', ['is_active' => false]);
        $this->place('پیش‌نویس', 'draft-place', 'nature', ['status' => 'draft']);

        $response = $this->get(route('tourism.index'));

        $response->assertOk()
            ->assertViewIs('frontend.tourism.index')
            ->assertSee('tourism-directory-page', false)
            ->assertSeeInOrder(['جاذبه طبیعی', 'جاذبه تاریخی', 'بازار', 'نوع ناسازگار'])
            ->assertDontSee('غیرفعال')
            ->assertDontSee('پیش‌نویس')
            ->assertViewHas('typeCounts', fn (array $counts) => $counts === [
                'all' => 4,
                'nature' => 1,
                'historic' => 1,
                'shopping' => 1,
            ]);
    }

    public function test_type_filters_and_invalid_type_falls_back_to_all(): void
    {
        $this->place('طبیعت', 'nature', 'nature');
        $this->place('تاریخ', 'historic', 'historic');
        $this->place('خرید', 'shopping', 'shopping');

        $this->get(route('tourism.index', ['type' => 'historic']))
            ->assertOk()
            ->assertSee('تاریخ')
            ->assertViewHas('activeType', 'historic')
            ->assertViewHas('places', fn ($places) => $places->pluck('title')->all() === ['تاریخ']);

        $this->get(route('tourism.index', ['type' => 'not-valid']))
            ->assertOk()
            ->assertSee('طبیعت')
            ->assertSee('تاریخ')
            ->assertSee('خرید')
            ->assertViewHas('activeType', null)
            ->assertViewHas('places', fn ($places) => $places->count() === 3);
    }

    public function test_ajax_returns_shared_results_counts_and_canonical_filter_url(): void
    {
        $this->place('طبیعت نخست', 'nature-one', 'nature');
        $this->place('طبیعت دوم', 'nature-two', 'nature');
        $this->place('بازار نخست', 'shopping-one', 'shopping');

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('tourism.index', ['type' => 'nature']));

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('active_type', 'nature')
            ->assertJsonPath('type_counts.all', 3)
            ->assertJsonPath('type_counts.nature', 2)
            ->assertJsonPath('type_counts.historic', 0)
            ->assertJsonPath('type_counts.shopping', 1)
            ->assertJsonPath('url', route('tourism.index', ['type' => 'nature']))
            ->assertJsonStructure(['html', 'total', 'active_type', 'type_counts', 'url']);

        $this->assertStringContainsString('data-tourism-results', $response->json('html'));
        $this->assertStringContainsString('طبیعت نخست', $response->json('html'));
        $this->assertStringNotContainsString('بازار نخست', $response->json('html'));
    }

    public function test_missing_images_use_tourism_placeholder_and_do_not_repeat_in_gallery(): void
    {
        $this->place('بدون تصویر', 'without-image', 'nature', [
            'image' => 'tourism/cards/missing.jpg',
            'featured_image' => 'assets/img/asnaf-gorgan-default.jpg',
            'gallery' => [['path' => 'assets/img/asnaf-gorgan-default.jpg']],
        ]);

        $this->get(route('tourism.index'))
            ->assertOk()
            ->assertSee('assets/img/tourism-placeholder.svg', false)
            ->assertDontSee('asnaf-gorgan-default.jpg', false)
            ->assertSee('هنوز تصویر معتبری برای گالری گردشگری ثبت نشده است.')
            ->assertDontSee('class="tourism-gallery-item"', false);
    }

    public function test_existing_card_image_uses_working_public_media_route_in_cards_intro_and_gallery(): void
    {
        Storage::disk('public')->put('tourism/cards/real.jpg', 'image');
        $this->place('دارای تصویر', 'with-image', 'nature', ['image' => 'tourism/cards/real.jpg']);
        $url = route('media.public.legacy', ['path' => 'tourism/cards/real.jpg']);

        $this->get(route('tourism.index'))
            ->assertOk()
            ->assertSee($url, false)
            ->assertSee('class="tourism-gallery-item"', false)
            ->assertDontSee('asnaf-gorgan-default.jpg', false);
    }

    public function test_empty_directory_has_accessible_empty_state_without_pagination(): void
    {
        $this->get(route('tourism.index'))
            ->assertOk()
            ->assertSee('جاذبه‌ای پیدا نشد')
            ->assertSee('نمایش همه جاذبه‌ها')
            ->assertDontSee('pagination', false);
    }

    public function test_related_places_use_the_same_valid_image_contract_as_the_directory(): void
    {
        Storage::disk('public')->put('tourism/cards/related.jpg', 'image');

        $current = $this->place('مکان اصلی', 'current-place', 'nature');
        $this->place('مکان مرتبط', 'related-place', 'nature', [
            'image' => 'tourism/cards/related.jpg',
            'featured_image' => 'assets/img/asnaf-gorgan-default.jpg',
        ]);

        $url = route('media.public.legacy', ['path' => 'tourism/cards/related.jpg']);

        $this->get(route('tourism.show', $current->slug))
            ->assertOk()
            ->assertSee('مکان‌های مرتبط')
            ->assertSee($url, false);
    }

    public function test_related_places_fill_remaining_slots_from_other_categories(): void
    {
        $primaryCategory = Category::query()->create([
            'title' => 'طبیعت',
            'slug' => 'related-primary-tourism',
            'type' => 'tourism',
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $otherCategory = Category::query()->create([
            'title' => 'سایر جاذبه‌ها',
            'slug' => 'related-secondary-tourism',
            'type' => 'tourism',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $current = $this->place('مکان اصلی', 'related-fill-current', 'nature', ['category_id' => $primaryCategory->id]);
        $sameCategory = $this->place('مرتبط اول', 'related-fill-one', 'nature', ['category_id' => $primaryCategory->id]);
        $second = $this->place('مرتبط دوم', 'related-fill-two', 'historic', ['category_id' => $otherCategory->id]);
        $third = $this->place('مرتبط سوم', 'related-fill-three', 'shopping', ['category_id' => $otherCategory->id]);
        $fourth = $this->place('مرتبط چهارم', 'related-fill-four', 'restaurant', ['category_id' => $otherCategory->id]);

        $this->get(route('tourism.show', $current->slug))
            ->assertOk()
            ->assertViewHas('relatedPlaces', function ($places) use ($sameCategory, $second, $third, $fourth) {
                return $places->count() === 4
                    && $places->first()->id === $sameCategory->id
                    && $places->pluck('id')->sort()->values()->all() === collect([
                        $sameCategory->id,
                        $second->id,
                        $third->id,
                        $fourth->id,
                    ])->sort()->values()->all();
            });
    }

    private function place(string $title, string $slug, string $type, array $attributes = []): TourismPlace
    {
        return TourismPlace::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'توضیح کوتاه واقعی جاذبه برای آزمون.',
            'tourism_type' => $type,
            'type' => $type,
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }
}
