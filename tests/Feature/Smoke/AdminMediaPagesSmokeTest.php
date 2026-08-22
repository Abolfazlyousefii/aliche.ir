<?php

namespace Tests\Feature\Smoke;

use App\Models\GuildUnion;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class AdminMediaPagesSmokeTest extends TestCase
{
    use BuildsAdminPayloads, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInAsSuperAdmin();
    }

    #[DataProvider('adminPageProvider')]
    public function test_media_related_admin_pages_render_without_an_exception(string $route): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('assets/admin/css/admin.css', false)
            ->assertSee('assets/admin/js/admin.js', false);
    }

    public static function adminPageProvider(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'pages' => ['admin.pages.index'],
            'page create' => ['admin.pages.create'],
            'posts' => ['admin.posts.index'],
            'post create' => ['admin.posts.create'],
            'media' => ['admin.media.index'],
            'galleries' => ['admin.galleries.index'],
            'unions' => ['admin.unions.index'],
            'users' => ['admin.users.index'],
            'union create' => ['admin.unions.create'],
            'union types' => ['admin.union-types.index'],
            'chamber members' => ['admin.chamber_members.index'],
            'commissions' => ['admin.commissions.index'],
            'advertisements' => ['admin.advertisements.index'],
            'videos' => ['admin.videos.index'],
            'electronic services' => ['admin.electronic_services.index'],
            'settings' => ['admin.settings.edit'],
        ];
    }

    #[DataProvider('paginatedAdminPageProvider')]
    public function test_admin_paginated_pages_accept_page_two_and_search_filters(string $route): void
    {
        $this->get(route($route, ['page' => 2, 'search' => 'آزمون']))
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public static function paginatedAdminPageProvider(): array
    {
        return [
            'pages' => ['admin.pages.index'],
            'posts' => ['admin.posts.index'],
            'media' => ['admin.media.index'],
            'galleries' => ['admin.galleries.index'],
            'unions' => ['admin.unions.index'],
            'users' => ['admin.users.index'],
        ];
    }

    public function test_deployed_media_picker_asset_is_utf8_rtl_and_free_of_known_mojibake(): void
    {
        $path = public_path('assets/admin/js/admin.js');
        $contents = file_get_contents($path);

        $this->assertIsString($contents);
        $this->assertFalse(str_starts_with($contents, "\xEF\xBB\xBF"));
        $this->assertTrue(mb_check_encoding($contents, 'UTF-8'));
        $this->assertStringContainsString('انتخاب تصویر از کتابخانه', $contents);
        $this->assertStringContainsString('فایل‌ها را اینجا رها کنید یا انتخاب کنید', $contents);
        $this->assertStringContainsString('جزئیات تصویر', $contents);
        $this->assertDoesNotMatchRegularExpression('/Ã|Â|â€|âœ|ط§|ط¨|ظ†|غŒ|ع©/u', $contents);
    }

    public function test_picker_selection_persists_on_page_post_and_union_create_and_update(): void
    {
        Storage::fake('public');
        $first = $this->media('media/first.webp');
        $second = $this->media('media/second.webp');

        $this->post(route('admin.pages.store'), [
            'title' => 'صفحه رسانه',
            'slug' => 'media-page',
            'body' => '<p>متن صفحه</p>',
            'featured_image_media_id' => $first->id,
            'template' => 'default',
            'status' => 'draft',
            'published_at' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect();
        $page = Page::query()->where('slug', 'media-page')->firstOrFail();
        $this->assertSame($first->path, $page->featured_image);

        $this->put(route('admin.pages.update', $page), [
            'title' => 'صفحه رسانه ویرایش‌شده',
            'slug' => 'media-page',
            'body' => '<p>متن صفحه</p>',
            'featured_image_media_id' => $second->id,
            'template' => 'default',
            'status' => 'draft',
            'published_at' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame($second->path, $page->fresh()->featured_image);

        $this->post(route('admin.posts.store'), $this->postPayload([
            'slug' => 'media-post',
            'featured_media_id' => $first->id,
            'gallery_media_ids' => [$second->id],
        ]))->assertRedirect();
        $post = Post::query()->where('slug', 'media-post')->firstOrFail();
        $this->assertSame($first->id, $post->featured_media_id);
        $this->assertSame([$second->id], $post->mediaGallery()->pluck('media.id')->all());

        $this->post(route('admin.unions.store'), $this->unionPayload([
            'slug' => 'media-union',
            'logo_media_id' => $first->id,
            'cover_image_media_id' => $second->id,
        ]))->assertRedirect();
        $union = GuildUnion::query()->where('slug', 'media-union')->firstOrFail();
        $this->assertSame($first->path, $union->logo);
        $this->assertSame($second->path, $union->cover_image);
    }

    public function test_page_create_accepts_an_omitted_nullable_published_at_field(): void
    {
        $this->post(route('admin.pages.store'), [
            'title' => 'صفحه بدون تاریخ',
            'slug' => 'page-without-date',
            'template' => 'default',
            'status' => 'draft',
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('pages', ['slug' => 'page-without-date']);
    }

    private function media(string $path): Media
    {
        Storage::disk('public')->put($path, 'image');

        return Media::query()->create([
            'file_name' => basename($path),
            'original_name' => basename($path),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 5,
            'width' => 100,
            'height' => 50,
        ]);
    }
}
