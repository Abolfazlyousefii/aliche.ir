<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\GuildUnion;
use App\Support\PublicFileUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_archive_shows_only_active_published_galleries_in_order_without_n_plus_one_relations(): void
    {
        $this->gallery('آلبوم دوم', 'second-gallery', ['sort_order' => 20]);
        $this->gallery('آلبوم نخست', 'first-gallery', ['sort_order' => 10]);
        $this->gallery('آلبوم غیرفعال', 'inactive-gallery', ['is_active' => false]);
        $this->gallery('آلبوم پیش‌نویس', 'draft-gallery', ['status' => 'draft']);
        $this->gallery('آلبوم آینده', 'future-gallery', ['published_at' => now()->addDay()]);

        $response = $this->get(route('galleries.index'));

        $response->assertOk()
            ->assertViewIs('frontend.galleries.index')
            ->assertSee('galleries-directory-page', false)
            ->assertSeeInOrder(['آلبوم نخست', 'آلبوم دوم'])
            ->assertDontSee('آلبوم غیرفعال')
            ->assertDontSee('آلبوم پیش‌نویس')
            ->assertDontSee('آلبوم آینده')
            ->assertViewHas('galleries', function ($galleries): bool {
                return $galleries->total() === 2
                    && $galleries->getCollection()->every(fn (Gallery $gallery): bool =>
                        $gallery->relationLoaded('images') && $gallery->relationLoaded('union')
                    );
            });
    }

    public function test_search_matches_title_description_and_related_union(): void
    {
        $union = GuildUnion::query()->create([
            'name' => 'اتحادیه پوشاک گرگان',
            'title' => 'اتحادیه پوشاک گرگان',
            'slug' => 'clothing-union',
            'is_active' => true,
        ]);
        $this->gallery('نشست تخصصی بازار', 'market-meeting', [
            'description' => 'گزارش تصویری جلسه فعالان صنفی',
            'union_id' => $union->id,
        ]);
        $this->gallery('رویداد دیگر', 'other-event');

        foreach (['نشست تخصصی', 'فعالان صنفی', 'پوشاک گرگان'] as $search) {
            $this->get(route('galleries.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('نشست تخصصی بازار')
                ->assertDontSee('رویداد دیگر')
                ->assertViewHas('galleries', fn ($galleries) => $galleries->total() === 1);
        }

        $this->get(route('galleries.index', ['search' => str_repeat('الف', 500)]))
            ->assertOk()
            ->assertSee('گالری‌ای پیدا نشد');
    }

    public function test_video_filter_is_not_fabricated_and_invalid_type_is_ignored(): void
    {
        $gallery = $this->gallery('آلبوم تصویری', 'image-gallery');
        $this->image($gallery, 'galleries/images/photo.jpg');

        $response = $this->get(route('galleries.index', ['type' => 'video']));
        $response->assertOk()
            ->assertSee('آلبوم تصویری')
            ->assertDontSee('data-galleries-type-tabs', false)
            ->assertViewHas('type', '')
            ->assertViewHas('typeCounts', fn ($counts) => $counts === [
                'all' => 1,
                'image' => 1,
                'video' => 0,
                'mixed' => 0,
            ]);
    }

    public function test_cover_priority_uses_existing_cover_then_existing_image_then_svg_fallback(): void
    {
        Storage::disk('public')->put('galleries/covers/real-cover.jpg', 'cover');
        Storage::disk('public')->put('galleries/images/real-image.jpg', 'image');

        $coverGallery = $this->gallery('دارای کاور', 'with-cover', ['cover_image' => 'galleries/covers/real-cover.jpg']);
        $imageGallery = $this->gallery('کاور از تصویر', 'cover-from-image', ['cover_image' => 'galleries/covers/missing.jpg']);
        $this->image($imageGallery, 'galleries/images/missing.jpg', 1);
        $this->image($imageGallery, 'galleries/images/real-image.jpg', 2);
        $fallbackGallery = $this->gallery('بدون کاور معتبر', 'fallback-cover', [
            'cover_image' => 'assets/img/asnaf-gorgan-default.jpg',
        ]);
        $remoteGallery = $this->gallery('کاور راه دور', 'remote-cover', [
            'cover_image' => 'https://media.test/broken-cover.jpg',
        ]);

        $response = $this->get(route('galleries.index'));
        $response->assertOk()
            ->assertSee(PublicFileUrl::make('galleries/covers/real-cover.jpg'), false)
            ->assertSee(PublicFileUrl::make('galleries/images/real-image.jpg'), false)
            ->assertSee('بدون کاور معتبر')
            ->assertSee('has-fallback', false)
            ->assertSee('gallery-directory-media-fallback', false)
            ->assertDontSee('asnaf-gorgan-default.jpg', false)
            ->assertSee('src="https://media.test/broken-cover.jpg"', false)
            ->assertSee('data-gallery-cover', false)
            ->assertSee(route('galleries.show', $coverGallery), false)
            ->assertSee(route('galleries.show', $fallbackGallery), false)
            ->assertSee(route('galleries.show', $remoteGallery), false);
    }

    public function test_real_image_counts_and_gallery_without_media_or_description_are_safe(): void
    {
        $withImages = $this->gallery('سه تصویر', 'three-images');
        foreach (range(1, 3) as $index) {
            $this->image($withImages, "galleries/images/image-{$index}.jpg", $index);
        }
        $this->gallery('بدون رسانه', 'without-media', ['description' => null]);

        $this->get(route('galleries.index'))
            ->assertOk()
            ->assertSee('۳ تصویر')
            ->assertSee('بدون رسانه')
            ->assertDontSee('توضیحی برای این گالری ثبت نشده است.');
    }

    public function test_ajax_pagination_returns_shared_results_counts_and_preserves_search(): void
    {
        foreach (range(1, 13) as $index) {
            $gallery = $this->gallery("آلبوم آزمون {$index}", "test-gallery-{$index}", ['sort_order' => $index]);
            $this->image($gallery, "galleries/images/test-{$index}.jpg");
        }
        $this->gallery('آلبوم غیرفعال ایجکس', 'inactive-ajax-gallery', ['is_active' => false]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('galleries.index', [
            'search' => 'آلبوم آزمون',
            'page' => 2,
            'type' => 'video',
        ]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 13)
            ->assertJsonPath('from', 13)
            ->assertJsonPath('to', 13)
            ->assertJsonPath('type_counts.all', 13)
            ->assertJsonPath('type_counts.image', 13)
            ->assertJsonPath('type_counts.video', 0)
            ->assertJsonPath('type_counts.mixed', 0)
            ->assertJsonStructure(['html', 'current_page', 'last_page', 'total', 'from', 'to', 'type_counts', 'url']);

        $html = $response->json('html');
        $this->assertStringContainsString('data-galleries-results', $html);
        $this->assertStringContainsString('galleries-directory-pagination', $html);
        $this->assertStringContainsString('search=%D8%A2%D9%84%D8%A8%D9%88%D9%85%20%D8%A2%D8%B2%D9%85%D9%88%D9%86', $html);
        $this->assertStringNotContainsString('type=video', $html);
        $this->assertStringNotContainsString('آلبوم غیرفعال ایجکس', $html);
        $this->assertStringNotContainsString('type=video', $response->json('url'));
    }

    public function test_empty_archive_has_professional_state_without_pagination(): void
    {
        $this->get(route('galleries.index'))
            ->assertOk()
            ->assertSee('گالری‌ای پیدا نشد')
            ->assertSee('نمایش همه گالری‌ها')
            ->assertDontSee('galleries-directory-pagination', false);
    }

    private function gallery(string $title, string $slug, array $attributes = []): Gallery
    {
        return Gallery::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'description' => 'توضیح واقعی آلبوم برای آزمون آرشیو.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function image(Gallery $gallery, string $path, int $sortOrder = 0): GalleryImage
    {
        return GalleryImage::query()->create([
            'gallery_id' => $gallery->id,
            'image' => $path,
            'sort_order' => $sortOrder,
        ]);
    }
}
