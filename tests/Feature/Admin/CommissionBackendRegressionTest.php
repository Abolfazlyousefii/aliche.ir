<?php

namespace Tests\Feature\Admin;

use App\Models\Commission;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommissionBackendRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_commission_forms_expose_media_library_picker(): void
    {
        $admin = $this->superAdmin();
        $commission = $this->commission();

        $this->actingAs($admin)
            ->get(route('admin.commissions.create'))
            ->assertOk()
            ->assertSee('data-media-select-target="image_media_id"', false);

        $this->actingAs($admin)
            ->get(route('admin.commissions.edit', $commission))
            ->assertOk()
            ->assertSee('data-media-select-target="image_media_id"', false);
    }

    public function test_selected_media_image_is_persisted_on_create_and_update(): void
    {
        $admin = $this->superAdmin();
        $first = $this->imageMedia('media/commission-first.jpg');
        $second = $this->imageMedia('media/commission-second.jpg');

        $this->actingAs($admin)
            ->post(route('admin.commissions.store'), $this->commissionPayload([
                'slug' => 'commission-media-create',
                'image_media_id' => $first->id,
            ]))
            ->assertSessionHasNoErrors();

        $commission = Commission::query()->where('slug', 'commission-media-create')->firstOrFail();
        $this->assertSame($first->path, $commission->image);

        $this->actingAs($admin)
            ->put(route('admin.commissions.update', $commission), $this->commissionPayload([
                'title' => $commission->title,
                'slug' => $commission->slug,
                'image_media_id' => $second->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame($second->path, $commission->refresh()->image);
    }

    public function test_commission_rejects_executable_attachment(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('admin.commissions.store'), $this->commissionPayload([
                'slug' => 'commission-invalid-attachment',
                'attachments' => [
                    UploadedFile::fake()->create('payload.php', 10, 'application/x-php'),
                ],
            ]))
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseMissing('commissions', ['slug' => 'commission-invalid-attachment']);
    }

    public function test_commission_session_rejects_executable_minutes_file(): void
    {
        $admin = $this->superAdmin();
        $commission = $this->commission();

        $this->actingAs($admin)
            ->post(route('admin.commissions.sessions.store', $commission), [
                'title' => 'جلسه آزمایشی',
                'minutes_file' => UploadedFile::fake()->create('minutes.php', 10, 'application/x-php'),
                'status' => 'draft',
                'is_active' => '1',
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('minutes_file');

        $this->assertDatabaseCount('commission_sessions', 0);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::query()->create(['name' => 'super-admin', 'label' => 'مدیرکل']);
        $user->roles()->attach($role);

        return $user;
    }

    private function imageMedia(string $path): Media
    {
        return Media::query()->create([
            'file_name' => basename($path),
            'original_name' => basename($path),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'width' => 1200,
            'height' => 800,
        ]);
    }

    private function commission(array $overrides = []): Commission
    {
        return Commission::query()->create(array_replace([
            'title' => 'کمیسیون آزمایشی',
            'slug' => 'commission-'.uniqid(),
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides));
    }

    private function commissionPayload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'کمیسیون آزمایشی',
            'slug' => 'commission-backend-test',
            'description' => '<p>توضیحات کمیسیون</p>',
            'members' => "عضو اول\nعضو دوم",
            'status' => 'draft',
            'published_at' => '',
            'rejected_reason' => '',
            'sort_order' => 0,
            'is_active' => '1',
            'tasks' => [],
        ], $overrides);
    }
}
