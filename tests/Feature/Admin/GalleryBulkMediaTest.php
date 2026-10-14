<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryBulkMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->signInAsSuperAdmin();
    }

    public function test_sixty_images_uploaded_in_small_requests_can_be_assigned_to_one_gallery_in_selected_order(): void
    {
        $ids = [];

        // A real sequence of 60 individually validated HTTP uploads (one image
        // per request, avoiding the PHP max_file_uploads/post_max_size limits).
        foreach (range(1, 60) as $number) {
            $response = $this->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image("batch-{$number}.jpg", 96, 72)],
            ]);
            $response->assertCreated()->assertJsonCount(1, 'items');
            $ids[] = $response->json('items.0.id');
        }

        $this->assertCount(60, array_unique($ids));
        $this->assertSame(60, Media::query()->count());

        // This is also how the bulk uploader's auto-selected library IDs are
        // submitted by the normal, permission-protected gallery form.
        $selected = array_reverse($ids);
        $this->post(route('admin.galleries.store'), [
            'title' => 'گالری شصت عکس', 'slug' => 'sixty-bulk-photos',
            'display_location' => 'both', 'status' => 'draft', 'is_active' => '1',
            'gallery_media_ids' => $selected,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $gallery = Gallery::query()->where('slug', 'sixty-bulk-photos')->firstOrFail();
        $actual = $gallery->images()->orderBy('sort_order')->pluck('image')->all();
        $expected = Media::query()->whereKey($selected)->get()->keyBy('id');
        $this->assertCount(60, $actual);
        $this->assertSame(
            array_map(fn ($id) => $expected[$id]->path, $selected),
            $actual,
            'Gallery order should follow the selected media IDs, not DB query order.'
        );
        $this->assertCount(60, array_unique($actual));
        $this->assertNotNull($gallery->fresh()->cover_image);
    }

    public function test_existing_gallery_adds_only_new_selected_images_and_preserves_old_ones(): void
    {
        $ids = collect(range(1, 4))->map(function ($index) {
            $media = Media::query()->create([
                'file_name' => "image-{$index}.jpg",
                'original_name' => "image-{$index}.jpg",
                'path' => "media/image-{$index}.jpg",
                'disk' => 'public',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 512,
                'width' => 100,
                'height' => 100,
            ]);
            Storage::disk('public')->put($media->path, 'image');

            return $media->id;
        })->all();

        $gallery = Gallery::query()->create([
            'title' => 'گالری موجود', 'slug' => 'gallery-existing-bulk',
            'status' => 'draft', 'is_active' => true,
        ]);
        $gallery->images()->create([
            'image' => Media::query()->findOrFail($ids[0])->path,
            'sort_order' => 10,
        ]);

        $this->put(route('admin.galleries.update', $gallery), [
            'title' => $gallery->title, 'slug' => $gallery->slug,
            'status' => 'draft', 'display_location' => 'both', 'is_active' => '1',
            'gallery_media_ids' => [$ids[0], $ids[2], $ids[2], $ids[1], $ids[3]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(
            Media::query()->whereKey([$ids[0], $ids[2], $ids[1], $ids[3]])
                ->get()->keyBy('id')->only([$ids[0], $ids[2], $ids[1], $ids[3]])->keys()->count(),
            $gallery->images()->count()
        );
        $this->assertSame(4, $gallery->images()->count());
        $this->assertSame(
            [$ids[0], $ids[2], $ids[1], $ids[3]],
            $gallery->images()->orderBy('sort_order')->get()
                ->map(fn ($row) => Media::query()->where('path', $row->image)->value('id'))->all()
        );
    }

    public function test_gallery_and_library_views_expose_bulk_upload_and_multi_select_controls(): void
    {
        $this->get(route('admin.galleries.create'))->assertOk()
            ->assertSee('data-mm2-gallery-form', false)
            ->assertSee('data-mm2-workspace', false)
            ->assertSee('data-media-select-multiple="true"', false);
        $gallery = Gallery::query()->create([
            'title' => 'آزمایش', 'slug' => 'bulk-edit', 'status' => 'draft',
        ]);
        $this->get(route('admin.galleries.edit', $gallery))->assertOk()
            ->assertSee('data-mm2-gallery-form', false)
            ->assertSee('data-mm2-workspace', false);
        $this->get(route('admin.media.index'))->assertOk()
            ->assertSee('data-media-dropzone', false)
            ->assertSee('data-media-bulk-progress', false)
            ->assertSee('multiple', false);
    }

    public function test_repeated_upload_receipt_does_not_create_duplicate_media(): void
    {
        $token = (string) \Illuminate\Support\Str::uuid();
        $first = $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('photo.jpg', 120, 80)],
            'upload_token' => $token,
        ])->assertCreated();

        $repeated = $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('photo.jpg', 120, 80)],
            'upload_token' => $token,
        ])->assertOk();

        $this->assertSame($first->json('items.0.id'), $repeated->json('items.0.id'));
        $this->assertSame(1, Media::query()->count());
    }

    public function test_gallery_can_reorder_existing_and_new_library_images_in_one_sequence(): void
    {
        $ids = collect(range(1, 3))->map(function ($index) {
            return Media::query()->create([
                'file_name' => "media-{$index}.jpg", 'original_name' => "media-{$index}.jpg",
                'path' => "media/media-{$index}.jpg", 'disk' => 'public',
                'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'size' => 500,
            ])->id;
        })->all();

        $gallery = Gallery::query()->create([
            'title' => 'گالری مرتب‌شده', 'slug' => 'ordered-gallery-v2',
            'status' => 'draft', 'is_active' => true,
        ]);
        $existing = $gallery->images()->create([
            'image' => Media::findOrFail($ids[0])->path, 'sort_order' => 10,
        ]);

        $this->put(route('admin.galleries.update', $gallery), [
            'title' => $gallery->title, 'slug' => $gallery->slug,
            'status' => 'draft', 'display_location' => 'both', 'is_active' => '1',
            'gallery_media_ids' => [$ids[1], $ids[2]],
            'gallery_order' => ["media:{$ids[2]}", "existing:{$existing->id}", "media:{$ids[1]}"],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([
            Media::findOrFail($ids[2])->path,
            Media::findOrFail($ids[0])->path,
            Media::findOrFail($ids[1])->path,
        ], $gallery->images()->orderBy('sort_order')->pluck('image')->all());
    }

    public function test_media_upload_permissions_and_safe_image_validation_still_apply(): void
    {
        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->createWithContent('evil.jpg', '<?php echo 1;')],
        ])->assertUnprocessable();
        $this->assertSame(0, Media::query()->count());

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->create('oversize.jpg', 5121, 'image/jpeg')],
        ])->assertUnprocessable();
        $this->assertSame(0, Media::query()->count());
    }
}
