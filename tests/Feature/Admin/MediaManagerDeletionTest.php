<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
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

    public function test_bulk_delete_preserves_used_and_shared_files_but_deletes_free_images(): void
    {
        $freeOne = $this->medium('free-one');
        $used = $this->medium('used');
        $freeTwo = $this->medium('free-two');
        $shared = $this->medium('shared');
        $duplicate = $this->medium('shared-copy', $shared->path);
        $external = $this->medium('external', 'https://example.test/imported.jpg');

        $gallery = Gallery::query()->create([
            'title' => 'گالری در حال استفاده', 'slug' => 'protected-bulk',
            'status' => 'draft', 'is_active' => true,
        ]);
        $gallery->images()->create(['image' => $used->path, 'sort_order' => 10]);

        $response = $this->deleteJson(route('admin.media.bulk_destroy'), [
            'ids' => [
                $freeOne->id, $used->id, $freeTwo->id, $shared->id,
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
        foreach ([$used, $shared, $duplicate, $external] as $protected) {
            $this->assertDatabaseHas('media', ['id' => $protected->id]);
        }
        foreach ([$used, $shared] as $protected) {
            Storage::disk('public')->assertExists($protected->path);
        }
        Storage::disk('public')->assertMissing($freeOne->path);
        Storage::disk('public')->assertMissing($freeTwo->path);
    }

    public function test_delete_is_forbidden_without_media_delete_even_if_user_can_view_and_upload(): void
    {
        $role = Role::query()->create(['name' => 'media-view-upload-test', 'label' => 'فقط رسانه']);
        foreach (['media.view', 'media.upload'] as $name) {
            $permission = Permission::query()->firstOrCreate(['name' => $name], ['label' => $name]);
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
