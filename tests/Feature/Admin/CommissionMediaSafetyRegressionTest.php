<?php

namespace Tests\Feature\Admin;

use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\CommissionTask;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommissionMediaSafetyRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_deleting_a_commission_does_not_remove_its_shared_media_files(): void
    {
        $this->actingAs($this->superAdmin());

        $paths = [
            'commissions/attachments/shared.pdf',
            'commission-sessions/minutes/shared-minutes.pdf',
            'commission-sessions/attachments/shared-session.pdf',
            'commission-sessions/images/shared-photo.jpg',
        ];

        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'shared content');
        }

        $commission = $this->commission([
            'attachments' => [['name' => 'پیوست', 'path' => $paths[0]]],
        ]);
        $commission->sessions()->create([
            'title' => 'جلسه',
            'minutes_file' => $paths[1],
            'attachments' => [['name' => 'پیوست جلسه', 'path' => $paths[2]]],
            'images' => [['name' => 'عکس', 'path' => $paths[3]]],
        ]);

        $this->delete(route('admin.commissions.destroy', $commission))
            ->assertRedirect(route('admin.commissions.index'));

        $this->assertDatabaseMissing('commissions', ['id' => $commission->id]);
        Storage::disk('public')->assertExists($paths);
    }

    public function test_deleting_a_commission_session_keeps_its_media_files(): void
    {
        $this->actingAs($this->superAdmin());

        $commission = $this->commission();
        $paths = [
            'commission-sessions/minutes/retained.pdf',
            'commission-sessions/attachments/retained.docx',
            'commission-sessions/images/retained.jpg',
        ];

        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'shared content');
        }

        $session = $commission->sessions()->create([
            'title' => 'جلسه قابل حذف',
            'minutes_file' => $paths[0],
            'attachments' => [['path' => $paths[1]]],
            'images' => [['path' => $paths[2]]],
        ]);

        $this->delete(route('admin.commissions.sessions.destroy', [$commission, $session]))
            ->assertRedirect(route('admin.commissions.sessions.index', $commission));

        $this->assertDatabaseMissing('commission_sessions', ['id' => $session->id]);
        Storage::disk('public')->assertExists($paths);
    }

    public function test_replacing_minutes_keeps_previous_file_and_persists_new_path(): void
    {
        $this->actingAs($this->superAdmin());

        $commission = $this->commission();
        $previousPath = 'commission-sessions/minutes/old-minutes.pdf';
        Storage::disk('public')->put($previousPath, 'old file');

        $session = $commission->sessions()->create([
            'title' => 'جلسه قبلی',
            'minutes_file' => $previousPath,
        ]);

        $this->put(route('admin.commissions.sessions.update', [$commission, $session]), [
            'title' => 'جلسه جدید',
            'minutes_file' => UploadedFile::fake()->create('new-minutes.pdf', 4, 'application/pdf'),
            'status' => 'draft',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $newPath = $session->fresh()->minutes_file;
        $this->assertNotSame($previousPath, $newPath);
        $this->assertNotEmpty($newPath);
        Storage::disk('public')->assertExists([$previousPath, $newPath]);
    }

    public function test_unlinking_a_commission_attachment_does_not_delete_the_file(): void
    {
        $this->actingAs($this->superAdmin());

        $path = 'commissions/attachments/retained.pdf';
        Storage::disk('public')->put($path, 'shared content');

        $commission = $this->commission([
            'attachments' => [['name' => 'فایل', 'path' => $path]],
        ]);

        $this->put(route('admin.commissions.update', $commission), [
            'title' => $commission->title,
            'slug' => $commission->slug,
            'status' => 'draft',
            'is_active' => '1',
            'existing_attachments' => [['delete' => '1']],
        ])->assertSessionHasNoErrors();

        $this->assertSame([], $commission->fresh()->attachments);
        Storage::disk('public')->assertExists($path);
    }

    public function test_edit_form_offers_five_blank_slots_after_existing_tasks(): void
    {
        $this->actingAs($this->superAdmin());

        $commission = $this->commission();

        foreach (range(1, 5) as $number) {
            CommissionTask::query()->create([
                'commission_id' => $commission->id,
                'title' => 'وظیفه '.$number,
                'is_active' => true,
                'sort_order' => $number,
            ]);
        }

        $this->get(route('admin.commissions.edit', $commission))
            ->assertOk()
            ->assertSee('name="tasks[9][title]"', false);
    }

    private function commission(array $overrides = []): Commission
    {
        return Commission::query()->create(array_replace([
            'title' => 'کمیسیون آزمایشی',
            'slug' => 'commission-media-safety-'.uniqid(),
            'status' => 'draft',
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::query()->create(['name' => 'super-admin', 'label' => 'مدیرکل']);
        $user->roles()->attach($role);

        return $user;
    }
}
