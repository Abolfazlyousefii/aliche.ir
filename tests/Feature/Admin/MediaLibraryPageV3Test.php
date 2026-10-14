<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaLibraryPageV3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsSuperAdmin();
    }

    private function medium(string $name, int $size, string $mime = 'image/jpeg'): Media
    {
        return Media::query()->create([
            'file_name' => $name.'.jpg',
            'original_name' => $name.'.jpg',
            'title' => $name,
            'path' => 'media/media-page-v3/'.$name.'.jpg',
            'disk' => 'public',
            'mime_type' => $mime,
            'extension' => 'jpg',
            'size' => $size,
        ]);
    }

    public function test_media_index_renders_distinct_delete_and_upload_controls(): void
    {
        $this->medium('example-image', 1024);

        $this->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('data-media-library-v3', false)
            ->assertSee('data-ml3-select-page', false)
            ->assertSee('data-ml3-delete-selected', false)
            ->assertSee('data-ml3-delete-one', false)
            ->assertSee('data-ml3-upload', false)
            ->assertSee('media-library-v3.js', false)
            ->assertSee('media-library-v3.css', false)
            ->assertSee('example-image');
    }

    public function test_sort_size_and_search_keep_query_parameters(): void
    {
        $small = $this->medium('find-small', 50);
        $large = $this->medium('find-large', 2048);
        $this->medium('unrelated', 4048);

        $response = $this->get(route('admin.media.index', [
            'search' => 'find-', 'sort' => 'largest', 'mime' => 'image/',
        ]))->assertOk();

        $response->assertSee('find-large')->assertSee('find-small')->assertDontSee('unrelated');
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'data-ml3-item="'.$small->id.'"'), strpos($html, 'data-ml3-item="'.$large->id.'"'));

        $response = $this->get(route('admin.media.index', [
            'search' => 'find-', 'sort' => 'smallest',
        ]))->assertOk();
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'data-ml3-item="'.$large->id.'"'), strpos($html, 'data-ml3-item="'.$small->id.'"'));
    }

    public function test_media_index_initially_shows_24_and_replaces_numbered_pagination_with_scroll(): void
    {
        foreach (range(1, 27) as $number) {
            $this->medium('infinite-'.$number, 1024 + $number);
        }

        $response = $this->get(route('admin.media.index'))->assertOk()
            ->assertSee('data-ml3-scroll', false)
            ->assertSee('data-ml3-scroll-sentinel', false)
            ->assertSee('data-ml3-scroll-more', false)
            ->assertDontSee('ml3-pagination', false);

        $this->assertSame(24, substr_count($response->getContent(), 'data-ml3-item="'));
        $this->assertStringContainsString('page=2', $response->getContent());
    }

    public function test_scroll_endpoint_returns_next_cards_only_with_same_permissions_and_filters(): void
    {
        foreach (range(1, 30) as $number) {
            $this->medium('scroll-photo-'.$number, $number * 100);
        }
        $this->medium('excluded-document', 200);

        $url = route('admin.media.index', [
            'search' => 'scroll-photo-', 'sort' => 'largest',
            'mime' => 'image/', 'page' => 2, 'media_scroll' => 1,
        ]);

        $response = $this->getJson($url)->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('total', 30)
            ->assertJsonPath('next_page_url', null);

        $html = $response->json('html');
        $this->assertSame(6, substr_count($html, 'data-ml3-item="'));
        $this->assertStringNotContainsString('excluded-document', $html);
        $this->assertStringNotContainsString('data-ml3-upload', $html);
        $this->assertStringContainsString('data-ml3-delete-one', $html);
        $this->assertStringContainsString('scroll-photo-1', $html);
        $this->assertStringNotContainsString('scroll-photo-30', $html);
    }

    public function test_scroll_endpoint_obeys_view_only_permission(): void
    {
        foreach (range(1, 26) as $number) {
            $this->medium('read-only-'.$number, 125);
        }
        $role = Role::query()->create(['name' => 'scroll-viewonly', 'label' => 'مشاهده اسکرول']);
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'media.view'], ['label' => 'مشاهده رسانه', 'group' => 'media']
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->roles()->sync([$role->id]);

        $response = $this->actingAs($viewer)->getJson(route('admin.media.index', [
            'page' => 2, 'media_scroll' => 1,
        ]))->assertOk();

        $this->assertSame(2, substr_count($response->json('html'), 'data-ml3-item="'));
        $this->assertStringNotContainsString('data-ml3-delete-one', $response->json('html'));
        $this->assertStringNotContainsString('data-ml3-upload', $response->json('html'));
    }

    public function test_scroll_query_without_json_accept_header_returns_normal_page(): void
    {
        $this->medium('fallback-scroll', 600);
        $this->get(route('admin.media.index', ['media_scroll' => 1]))
            ->assertOk()
            ->assertSee('data-media-library-v3', false);
    }

    public function test_view_only_user_does_not_receive_upload_or_delete_buttons(): void
    {
        $role = Role::query()->create(['name' => 'media-viewonly-v3', 'label' => 'کتابخانه فقط خواندنی']);
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'media.view'],
            ['label' => 'مشاهده رسانه', 'group' => 'media']
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->roles()->sync([$role->id]);

        $this->actingAs($viewer)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('data-media-library-v3', false)
            ->assertDontSee('data-ml3-delete-selected', false)
            ->assertDontSee('data-ml3-delete-one', false)
            ->assertDontSee('data-ml3-upload', false);
    }
}
