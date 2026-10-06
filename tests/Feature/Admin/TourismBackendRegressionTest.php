<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Media;
use App\Models\TourismPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourismBackendRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_forms_expose_tourism_media_library_controls(): void
    {
        $this->signInAsSuperAdmin();

        $place = TourismPlace::query()->create([
            'title' => 'مکان برای فرم',
            'slug' => 'tourism-media-form',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        foreach ([route('admin.tourism.create'), route('admin.tourism.edit', $place)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-media-select-target="image_media_id"', false)
                ->assertSee('data-media-select-target="featured_image_media_id"', false)
                ->assertSee('data-media-select-target="gallery_images_media_ids"', false)
                ->assertSee('data-media-select-multiple="true"', false);
        }
    }

    public function test_admin_update_persists_selected_card_featured_and_gallery_media(): void
    {
        $this->signInAsSuperAdmin();

        $place = TourismPlace::query()->create([
            'title' => 'مکان قابل ویرایش',
            'slug' => 'tourism-media-update',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'gallery' => [],
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $card = $this->image('media/update-card.jpg');
        $featured = $this->image('media/update-featured.jpg');
        $gallery = $this->image('media/update-gallery.jpg');

        $this->put(route('admin.tourism.update', $place), $this->payload([
            'title' => $place->title,
            'slug' => $place->slug,
            'image_media_id' => $card->id,
            'featured_image_media_id' => $featured->id,
            'gallery_images_media_ids' => [$gallery->id],
        ]))->assertSessionHasNoErrors();

        $place->refresh();

        $this->assertSame($card->path, $place->image);
        $this->assertSame($featured->path, $place->featured_image);
        $this->assertSame($gallery->path, $place->gallery[0]['path'] ?? null);
    }

    public function test_admin_index_filters_by_tourism_type(): void
    {
        $this->signInAsSuperAdmin();

        TourismPlace::query()->create([
            'title' => 'جاذبه طبیعی',
            'slug' => 'tourism-filter-nature',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        TourismPlace::query()->create([
            'title' => 'رستوران گردشگری',
            'slug' => 'tourism-filter-restaurant',
            'tourism_type' => 'restaurant',
            'type' => 'restaurant',
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->get(route('admin.tourism.index', ['tourism_type' => 'restaurant']))
            ->assertOk()
            ->assertSee('رستوران گردشگری')
            ->assertDontSee('جاذبه طبیعی');
    }

    public function test_gallery_media_selection_preserves_order_and_removes_duplicates(): void
    {
        $this->signInAsSuperAdmin();

        $first = $this->image('media/gallery-first.jpg');
        $second = $this->image('media/gallery-second.jpg');
        $third = $this->image('media/gallery-third.jpg');

        $this->post(route('admin.tourism.store'), $this->payload([
            'slug' => 'tourism-gallery-order',
            'gallery_images_media_ids' => [$third->id, $first->id, $third->id, $second->id],
        ]))->assertSessionHasNoErrors();

        $place = TourismPlace::query()->where('slug', 'tourism-gallery-order')->firstOrFail();

        $this->assertSame(
            [$third->path, $first->path, $second->path],
            collect($place->gallery)->pluck('path')->all()
        );
    }

    public function test_admin_rejects_non_tourism_category(): void
    {
        $this->signInAsSuperAdmin();

        $category = Category::query()->create([
            'title' => 'دسته نامرتبط',
            'slug' => 'non-tourism-category',
            'type' => 'video',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->post(route('admin.tourism.store'), $this->payload([
            'slug' => 'tourism-wrong-category',
            'category_id' => $category->id,
        ]))->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('tourism_places', ['slug' => 'tourism-wrong-category']);
    }

    public function test_admin_rejects_non_image_media_selections(): void
    {
        $this->signInAsSuperAdmin();

        $document = Media::query()->create([
            'file_name' => 'guide.pdf',
            'original_name' => 'guide.pdf',
            'path' => 'media/guide.pdf',
            'disk' => 'public',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 2048,
        ]);

        $this->post(route('admin.tourism.store'), $this->payload([
            'slug' => 'tourism-invalid-media',
            'image_media_id' => $document->id,
            'featured_image_media_id' => $document->id,
            'gallery_images_media_ids' => [$document->id],
        ]))
            ->assertSessionHasErrors([
                'image_media_id',
                'featured_image_media_id',
                'gallery_images_media_ids.0',
            ]);

        $this->assertDatabaseMissing('tourism_places', ['slug' => 'tourism-invalid-media']);
    }

    public function test_admin_rejects_unsafe_map_url(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.tourism.store'), $this->payload([
            'slug' => 'tourism-unsafe-map',
            'map_url' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors('map_url');

        $this->assertDatabaseMissing('tourism_places', ['slug' => 'tourism-unsafe-map']);
    }

    public function test_legacy_unsafe_map_url_is_not_exposed_by_model(): void
    {
        $place = TourismPlace::query()->create([
            'title' => 'مکان قدیمی',
            'slug' => 'legacy-unsafe-tourism-map',
            'tourism_type' => 'nature',
            'type' => 'nature',
            'map_url' => 'javascript:alert(1)',
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->assertNull($place->map_url);
        $this->assertSame('javascript:alert(1)', $place->getRawOriginal('map_url'));
    }

    public function test_valid_category_media_gallery_and_map_are_persisted(): void
    {
        $this->signInAsSuperAdmin();

        $category = Category::query()->create([
            'title' => 'جاذبه‌های گردشگری',
            'slug' => 'tourism-category-valid',
            'type' => 'tourism',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $card = $this->image('media/tourism-card.jpg');
        $featured = $this->image('media/tourism-featured.jpg');
        $gallery = $this->image('media/tourism-gallery.jpg');

        $this->post(route('admin.tourism.store'), $this->payload([
            'slug' => 'tourism-valid-backend',
            'category_id' => $category->id,
            'image_media_id' => $card->id,
            'featured_image_media_id' => $featured->id,
            'gallery_images_media_ids' => [$gallery->id],
            'map_url' => 'https://maps.google.com/?q=36.84,54.44',
        ]))->assertSessionHasNoErrors();

        $place = TourismPlace::query()->where('slug', 'tourism-valid-backend')->firstOrFail();

        $this->assertSame($category->id, $place->category_id);
        $this->assertSame($card->path, $place->image);
        $this->assertSame($featured->path, $place->featured_image);
        $this->assertSame('https://maps.google.com/?q=36.84,54.44', $place->map_url);
        $this->assertSame($gallery->path, $place->gallery[0]['path'] ?? null);
        $this->assertStringContainsString('tourism-featured.jpg', $place->featured_image_url);
        $this->assertStringNotContainsString('tourism-card.jpg', $place->featured_image_url);
    }

    private function image(string $path): Media
    {
        return Media::query()->create([
            'file_name' => basename($path),
            'original_name' => basename($path),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 4096,
            'width' => 1200,
            'height' => 800,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'مکان گردشگری آزمایشی',
            'slug' => 'tourism-backend-regression',
            'description' => '<p>توضیحات کامل مکان</p>',
            'short_description' => 'توضیح کوتاه مکان',
            'category_id' => null,
            'badge' => '',
            'location' => 'گرگان',
            'tourism_type' => 'nature',
            'address' => 'گرگان',
            'map_url' => '',
            'latitude' => '',
            'longitude' => '',
            'phone' => '',
            'working_hours' => '',
            'visit_price' => '',
            'status' => 'draft',
            'published_at' => '',
            'rejected_reason' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ], $overrides);
    }
}
