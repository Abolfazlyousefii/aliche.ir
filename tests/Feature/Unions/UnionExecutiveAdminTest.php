<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class UnionExecutiveAdminTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_create_and_edit_forms_have_separate_executive_fields(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union();

        foreach ([route('admin.unions.create'), route('admin.unions.edit', $union)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('name="executive_name"', false)
                ->assertSee('name="executive_position"', false)
                ->assertSee('name="executive_image"', false)
                ->assertSee('data-media-select-target="executive_image_media_id"', false);
        }
    }

    public function test_executive_fields_and_selected_photo_persist_on_create_and_edit(): void
    {
        $this->signInAsSuperAdmin();

        $portrait = Media::query()->create([
            'file_name' => 'executive.jpg',
            'original_name' => 'executive.jpg',
            'path' => 'media/union/executive.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 2048,
        ]);

        $this->post(route('admin.unions.store'), $this->unionPayload([
            'title' => 'اتحادیه دارای مدیر اجرایی',
            'slug' => 'union-executive-persist',
            'manager_name' => 'رئیس اتحادیه',
            'executive_name' => 'مدیر اجرایی اول',
            'executive_position' => 'مدیر اجرایی',
            'executive_image_media_id' => $portrait->id,
        ]))->assertSessionHasNoErrors();

        $union = GuildUnion::query()->where('slug', 'union-executive-persist')->firstOrFail();
        $this->assertSame('مدیر اجرایی اول', $union->executive_name);
        $this->assertSame('مدیر اجرایی', $union->executive_position);
        $this->assertSame($portrait->path, $union->executive_image);
        $this->assertTrue($portrait->inUse(), 'Media in use as an executive image should be protected.');

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'manager_name' => $union->manager_name,
            'executive_name' => 'مدیر اجرایی دوم',
            'executive_position' => 'مسئول اجرایی',
        ]))->assertSessionHasNoErrors();

        $union->refresh();
        $this->assertSame('مدیر اجرایی دوم', $union->executive_name);
        $this->assertSame('مسئول اجرایی', $union->executive_position);
        $this->assertSame($portrait->path, $union->executive_image);
    }

    public function test_non_image_media_cannot_be_assigned_as_executive_portrait(): void
    {
        $this->signInAsSuperAdmin();
        $document = Media::query()->create([
            'file_name' => 'document.pdf',
            'original_name' => 'document.pdf',
            'path' => 'media/document.pdf',
            'disk' => 'public',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 4096,
        ]);

        $this->post(route('admin.unions.store'), $this->unionPayload([
            'title' => 'اتحادیه نامعتبر',
            'slug' => 'invalid-executive-picture',
            'executive_name' => 'مدیر تست',
            'executive_image_media_id' => $document->id,
        ]))->assertSessionHasErrors('executive_image_media_id');

        $this->assertDatabaseMissing('unions', ['slug' => 'invalid-executive-picture']);
    }

    public function test_unlinking_executive_photo_preserves_underlying_media(): void
    {
        $this->signInAsSuperAdmin();
        Storage::fake('public');

        $imagePath = 'media/union/shared-executive.jpg';
        Storage::disk('public')->put($imagePath, 'shared file');
        $union = $this->union([
            'executive_name' => 'مدیر اجرایی',
            'executive_image' => $imagePath,
        ]);

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'executive_name' => $union->executive_name,
            'remove_executive_image' => '1',
        ]))->assertSessionHasNoErrors();

        $this->assertNull($union->fresh()->executive_image);
        Storage::disk('public')->assertExists($imagePath);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه تست',
            'title' => 'اتحادیه تست',
            'slug' => 'union-executive-'.uniqid(),
            'is_active' => true,
        ], $overrides));
    }
}
