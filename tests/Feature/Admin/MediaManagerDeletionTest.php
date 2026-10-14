<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaManagerDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->signInAsSuperAdmin();
    }

    private function medium(string $name, ?string $path = null): Media
    {
        $path ??= 'media/2026/10/'.$name.'.jpg';
        $media = Media::query()->create([
            'file_name' => $name.'.jpg', 'original_name' => $name.'.jpg',
            'path' => $path, 'disk' => 'public',
            'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'size' => 250,
        ]);
        if (! str_starts_with($path, 'http') && ! str_starts_with($path, 'assets/')) {
            Storage::disk('public')->put($path, 'test-file-'.$name);
        }

        return $media;
    }

    public function test_single_json_delete_removes_unused_media_and_image_variants(): void
    {
        $media = $this->medium('single');
        $variant = 'media/2026/10/single-400w.jpg';
        Storage::disk('public')->put($variant, 'thumbnail');

        $this->deleteJson(route('admin.media.destroy', $media))
            ->assertOk()->assertJsonPath('status', 'deleted')
            ->assertJsonPath('id', $media->id);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->path);
        Storage::disk('public')->assertMissing($variant);
    }

    public function test_single_delete_refuses_image_attached_to_gallery(): void
    {
        $media = $this->medium('in-use');
        $gallery = Gallery::query()->create([
            'title' => 'گالری آزمایشی', 'slug' => 'protected-images',
            'status' => 'draft', 'is_active' => true,
        ]);
        $gallery->images()->create(['image' => $media->path, 'sort_order' => 10]);

        $this->deleteJson(route('admin.media.destroy', $media))
            ->assertStatus(409)->assertJsonPath('status', 'blocked');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_single_delete_from_current_gallery_detaches_photo_and_clears_last_cover(): void
    {
        $media = $this->medium('owned-by-current-gallery');
        $gallery = Gallery::query()->create([
            'title' => 'گالری تک تصویر', 'slug' => 'only-one-photo',
            'cover_image' => $media->path, 'status' => 'draft', 'is_active' => true,
        ]);
        $image = $gallery->images()->create(['image' => $media->path, 'sort_order' => 10]);

        $this->deleteJson(route('admin.media.destroy', $media).'?gallery_id='.$gallery->id)
            ->assertOk()->assertJsonPath('status', 'deleted')
            ->assertJsonPath('detached_gallery_image_ids.0', $image->id);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
        $this->assertNull($gallery->fresh()->cover_image);
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_bulk_delete_detaches_current_gallery_and_replaces_cover_with_remaining_image(): void
    {
        $cover = $this->medium('cover-to-delete');
        $other = $this->medium('gallery-to-delete');
        $remaining = $this->medium('gallery-remains');
        $gallery = Gallery::query()->create([
            'title' => 'گالری اصلی', 'slug' => 'delete-photos-from-current',
            'cover_image' => $cover->path, 'status' => 'draft', 'is_active' => true,
        ]);
        $first = $gallery->images()->create(['image' => $cover->path, 'sort_order' => 10]);
        $second = $gallery->images()->create(['image' => $other->path, 'sort_order' => 20]);
        $third = $gallery->images()->create(['image' => $remaining->path, 'sort_order' => 30]);

        $this->deleteJson(route('admin.media.bulk_destroy'), [
            'ids' => [$cover->id, $other->id],
            'gallery_id' => $gallery->id,
        ])->assertOk()
            ->assertJsonPath('deleted.0', $cover->id)
            ->assertJsonPath('deleted.1', $other->id);

        $this->assertDatabaseMissing('gallery_images', ['id' => $first->id]);
        $this->assertDatabaseMissing('gallery_images', ['id' => $second->id]);
        $this->assertDatabaseHas('gallery_images', ['id' => $third->id]);
        $this->assertSame($remaining->path, $gallery->fresh()->cover_image);
        Storage::disk('public')->assertExists($remaining->path);
        Storage::disk('public')->assertMissing($cover->path);
        Storage::disk('public')->assertMissing($other->path);
    }

    public function test_other_gallery_references_block_delete_even_when_current_gallery_is_given(): void
    {
        $media = $this->medium('shared-between-galleries');
        $current = Gallery::query()->create([
            'title' => 'گالری فعلی', 'slug' => 'current-media-gallery',
            'cover_image' => $media->path, 'status' => 'draft',
        ]);
        $other = Gallery::query()->create([
            'title' => 'گالری دوم', 'slug' => 'another-media-gallery',
            'status' => 'draft',
        ]);
        $currentImage = $current->images()->create(['image' => $media->path, 'sort_order' => 10]);
        $other->images()->create(['image' => $media->path, 'sort_order' => 10]);

        $this->deleteJson(route('admin.media.destroy', $media).'?gallery_id='.$current->id)
            ->assertStatus(409)->assertJsonPath('status', 'blocked');

        $this->assertDatabaseHas('gallery_images', ['id' => $currentImage->id]);
        $this->assertSame($media->path, $current->fresh()->cover_image);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_gallery_scoped_delete_requires_both_delete_and_gallery_edit_permissions(): void
    {
        $role = Role::query()->create(['name' => 'only-media-deletion', 'label' => 'حذف رسانه']);
        foreach (['media.delete', 'media.view'] as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name], ['label' => $name, 'group' => 'media']
            );
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->sync([$role->id]);
        $media = $this->medium('gallery-needs-edit-permission');
        $gallery = Gallery::query()->create([
            'title' => 'گالری محدود', 'slug' => 'scope-needs-edit',
            'status' => 'draft',
        ]);
        $image = $gallery->images()->create(['image' => $media->path, 'sort_order' => 10]);

        $this->actingAs($user)
            ->deleteJson(route('admin.media.destroy', $media).'?gallery_id='.$gallery->id)
            ->assertForbidden();
        $this->deleteJson(route('admin.media.bulk_destroy'), [
            'ids' => [$media->id], 'gallery_id' => $gallery->id,
        ])->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertDatabaseHas('gallery_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists($media->path);
    }

    private function commissionSessionWith(array $attributes): CommissionSession
    {
        $commission = Commission::query()->create([
            'title' => 'کمیسیون آزمون حذف رسانه',
            'slug' => 'test-commission-'.strtolower(\Illuminate\Support\Str::random(8)),
            'status' => 'draft',
        ]);

        return CommissionSession::query()->create([
            'commission_id' => $commission->id,
            'title' => 'صورت‌جلسه آزمون',
            'status' => 'draft',
            ...$attributes,
        ]);
    }

    public function test_commission_session_minutes_file_blocks_permanent_media_deletion(): void
    {
        $medium = $this->medium('minutes-reference');
        $this->commissionSessionWith(['minutes_file' => $medium->path]);

        $this->deleteJson(route('admin.media.destroy', $medium))
            ->assertStatus(409)->assertJsonPath('status', 'blocked');

        $this->assertDatabaseHas('media', ['id' => $medium->id]);
        Storage::disk('public')->assertExists($medium->path);
    }

    public function test_commission_session_json_images_and_attachments_block_deletion_even_for_current_gallery(): void
    {
        $medium = $this->medium('json-reference');
        $gallery = Gallery::query()->create([
            'title' => 'گالری آزمایشی', 'slug' => 'gallery-commission-ref',
            'status' => 'draft', 'cover_image' => $medium->path,
        ]);
        $galleryImage = $gallery->images()->create([
            'image' => $medium->path, 'sort_order' => 10,
        ]);
        $this->commissionSessionWith([
            'attachments' => [['name' => 'test.jpg', 'path' => $medium->path]],
            'images' => [['name' => 'test.jpg', 'path' => $medium->path]],
        ]);

        $this->deleteJson(route('admin.media.destroy', $medium).'?gallery_id='.$gallery->id)
            ->assertStatus(409)->assertJsonPath('status', 'blocked');

        $this->assertDatabaseHas('gallery_images', ['id' => $galleryImage->id]);
        $this->assertSame($medium->path, $gallery->fresh()->cover_image);
        Storage::disk('public')->assertExists($medium->path);
    }

    public function test_escaped_json_media_path_in_commission_session_blocks_delete(): void
    {
        $medium = $this->medium('nested-dir-photo', 'media/2026/10/nested-dir-photo.jpg');
        $session = $this->commissionSessionWith(['description' => 'صورت‌جلسه']);

        // Some legacy JSON serializers persist paths with escaped slashes.
        $escaped = str_replace('/', '\\/', $medium->path);
        \Illuminate\Support\Facades\DB::table('commission_sessions')
            ->where('id', $session->id)->update([
                'images' => json_encode([['path' => $escaped]], JSON_UNESCAPED_SLASHES),
            ]);

        $this->deleteJson(route('admin.media.destroy', $medium))
            ->assertStatus(409)->assertJsonPath('status', 'blocked');
        Storage::disk('public')->assertExists($medium->path);
    }

    public function test_bulk_delete_preserves_used_and_external_files_but_deletes_free_images(): void
    {
        $freeOne = $this->medium('free-one');
        $used = $this->medium('used');
        $freeTwo = $this->medium('free-two');
        $asset = $this->medium('theme-asset', 'assets/img/site-banner.jpg');
        $external = $this->medium('external', 'https://example.test/imported.jpg');

        $gallery = Gallery::query()->create([
            'title' => 'گالری در حال استفاده', 'slug' => 'protected-bulk',
            'status' => 'draft', 'is_active' => true,
        ]);
        $gallery->images()->create(['image' => $used->path, 'sort_order' => 10]);

        $response = $this->deleteJson(route('admin.media.bulk_destroy'), [
            'ids' => [
                $freeOne->id, $used->id, $freeTwo->id, $asset->id,
                $external->id, 999999, $freeOne->id,
            ],
        ])->assertOk();

        $this->assertEqualsCanonicalizing(
            [$freeOne->id, $freeTwo->id],
            $response->json('deleted')
        );
        $this->assertCount(3, $response->json('blocked'));
        $this->assertSame(999999, $response->json('missing.0.id'));
        $this->assertCount(0, $response->json('failed'));
        $this->assertDatabaseMissing('media', ['id' => $freeOne->id]);
        $this->assertDatabaseMissing('media', ['id' => $freeTwo->id]);
        foreach ([$used, $asset, $external] as $protected) {
            $this->assertDatabaseHas('media', ['id' => $protected->id]);
        }
        foreach ([$used] as $protected) {
            Storage::disk('public')->assertExists($protected->path);
        }
        Storage::disk('public')->assertMissing($freeOne->path);
        Storage::disk('public')->assertMissing($freeTwo->path);
    }

    public function test_delete_is_forbidden_without_media_delete_even_if_user_can_view_and_upload(): void
    {
        $role = Role::query()->create(['name' => 'media-view-upload-test', 'label' => 'فقط رسانه']);
        foreach (['media.view', 'media.upload'] as $name) {
            $permission = Permission::query()->firstOrCreate(['name' => $name], ['label' => $name, 'group' => 'media']);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->roles()->sync([$role->id]);
        $media = $this->medium('protected-permission');

        $this->actingAs($viewer)->get(route('admin.media.index'))->assertOk()
            ->assertSee('window.adminMediaCanDelete = false', false);
        $this->deleteJson(route('admin.media.destroy', $media))->assertForbidden();
        $this->deleteJson(route('admin.media.bulk_destroy'), ['ids' => [$media->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_bulk_delete_validates_ids_and_refuses_oversized_batches(): void
    {
        $this->deleteJson(route('admin.media.bulk_destroy'), ['ids' => ['not-id']])
            ->assertUnprocessable();
        $this->deleteJson(route('admin.media.bulk_destroy'), [
            'ids' => range(1, 51),
        ])->assertUnprocessable();
        $this->deleteJson(route('admin.media.bulk_destroy'), ['ids' => []])
            ->assertUnprocessable();
    }
}
