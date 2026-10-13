<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaDetailKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_details_show_titles_on_white_content_area_not_the_dark_header(): void
    {
        $gallery = $this->gallery('photo-detail-white');
        $video = $this->video('video-detail-white');

        foreach ([
            route('galleries.show', $gallery->slug) => $gallery->title,
            route('videos.show', $video->slug) => $video->title,
        ] as $url => $title) {
            $this->get($url)->assertOk()
                ->assertSee('media-detail-heading', false)
                ->assertSee('<h1>'.$title.'</h1>', false)
                ->assertDontSee('page-header-alt', false)
                ->assertDontSee('class="page-header', false);
        }
    }

    public function test_each_media_type_displays_its_own_clean_tags_and_escaped_seo_meta(): void
    {
        $gallery = $this->gallery('photo-keyword-test', [
            'meta_keywords' => 'گالری صنفی، گرگان, گالری صنفی, <script>alert(1)</script>',
        ]);
        $video = $this->video('video-keyword-test', [
            'meta_keywords' => 'ویدئو، نشست خبری, ویدئو',
        ]);

        $galleryPage = $this->get(route('galleries.show', $gallery->slug));
        $galleryPage->assertOk()
            ->assertSee('name="keywords"', false)
            ->assertSee('گالری صنفی')
            ->assertSee('گرگان')
            ->assertDontSee('ویدئو')
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(['گالری صنفی', 'گرگان', '<script>alert(1)</script>'], $gallery->tags);

        $videoPage = $this->get(route('videos.show', $video->slug));
        $videoPage->assertOk()
            ->assertSee('name="keywords"', false)
            ->assertSee('نشست خبری')
            ->assertDontSee('گالری صنفی');
        $this->assertSame(['ویدئو', 'نشست خبری'], $video->tags);
    }

    public function test_admin_can_create_and_edit_gallery_keywords_independently(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.galleries.create'))->assertOk()->assertSee('name="meta_keywords"', false);

        $this->post(route('admin.galleries.store'), [
            'title' => 'گالری برچسب', 'slug' => 'gallery-keyword-admin',
            'display_location' => 'both', 'status' => 'draft', 'is_active' => '1',
            'meta_keywords' => 'اصناف، گرگان، اصناف',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $gallery = Gallery::query()->where('slug', 'gallery-keyword-admin')->firstOrFail();
        $this->assertSame('اصناف، گرگان', $gallery->meta_keywords);

        $this->get(route('admin.galleries.edit', $gallery))
            ->assertOk()->assertSee('name="meta_keywords"', false)->assertSee('اصناف، گرگان');

        $this->put(route('admin.galleries.update', $gallery), [
            'title' => $gallery->title, 'slug' => $gallery->slug,
            'display_location' => 'both', 'status' => 'draft', 'is_active' => '1',
            'meta_keywords' => 'بازار, گزارش تصویری',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(['بازار', 'گزارش تصویری'], $gallery->fresh()->tags);
    }

    public function test_admin_can_create_edit_and_clear_video_keywords(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.videos.create'))->assertOk()->assertSee('name="meta_keywords"', false);
        $this->post(route('admin.videos.store'), [
            'title' => 'ویدئوی برچسب', 'slug' => 'video-keyword-admin',
            'video_type' => 'aparat', 'aparat_url' => 'https://www.aparat.com/v/xyz123',
            'status' => 'draft', 'is_active' => '1',
            'meta_keywords' => 'ویدئو، بازار\nنشست خبری',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $video = Video::query()->where('slug', 'video-keyword-admin')->firstOrFail();
        $this->assertSame(['ویدئو', 'بازار', 'نشست خبری'], $video->tags);
        $this->get(route('admin.videos.edit', $video))
            ->assertOk()->assertSee('name="meta_keywords"', false)->assertSee('ویدئو، بازار');

        $this->put(route('admin.videos.update', $video), [
            'title' => $video->title, 'slug' => $video->slug,
            'video_type' => 'aparat', 'aparat_url' => $video->aparat_url,
            'status' => 'draft', 'is_active' => '1', 'meta_keywords' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNull($video->fresh()->meta_keywords);
        $this->assertSame([], $video->fresh()->tags);
    }

    public function test_keyword_field_is_optional_validated_and_migration_preserves_old_records(): void
    {
        $this->assertTrue(Schema::hasColumn('galleries', 'meta_keywords'));
        $this->assertTrue(Schema::hasColumn('videos', 'meta_keywords'));
        $this->assertSame([], $this->gallery('old-gallery-no-keywords')->tags);
        $this->assertSame([], $this->video('old-video-no-keywords')->tags);

        $this->signInAsSuperAdmin();
        $this->post(route('admin.galleries.store'), [
            'title' => 'ناقص', 'slug' => 'too-many-gallery-keywords',
            'display_location' => 'both', 'status' => 'draft', 'is_active' => '1',
            'meta_keywords' => str_repeat('الف', 1100),
        ])->assertSessionHasErrors('meta_keywords');

        $this->post(route('admin.videos.store'), [
            'title' => 'ناقص', 'slug' => 'too-many-video-keywords',
            'video_type' => 'aparat', 'aparat_url' => 'https://www.aparat.com/v/xyz123',
            'status' => 'draft', 'is_active' => '1',
            'meta_keywords' => str_repeat('ب', 1100),
        ])->assertSessionHasErrors('meta_keywords');
    }

    private function gallery(string $slug, array $overrides = []): Gallery
    {
        return Gallery::query()->create(array_replace([
            'title' => 'گالری آزمایشی',
            'slug' => $slug,
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->subMinute(),
        ], $overrides));
    }

    private function video(string $slug, array $overrides = []): Video
    {
        return Video::query()->create(array_replace([
            'title' => 'ویدئوی آزمایشی',
            'slug' => $slug,
            'video_type' => 'aparat',
            'aparat_url' => 'https://www.aparat.com/v/xyz123',
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->subMinute(),
        ], $overrides));
    }
}
