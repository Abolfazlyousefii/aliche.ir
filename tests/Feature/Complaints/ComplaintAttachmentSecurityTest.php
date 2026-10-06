<?php

namespace Tests\Feature\Complaints;

use App\Models\Complaint;
use App\Models\GuildUnion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintAttachmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('features.complaints_enabled', true);
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_new_complaint_attachment_is_private_and_download_requires_authorization(): void
    {
        $union = $this->union('own-complaint-union');

        $this->post(route('complaints.store'), [
            'union_id' => $union->id,
            'full_name' => 'شاکی آزمایشی',
            'mobile' => '09123456789',
            'subject' => 'موضوع شکایت',
            'body' => 'شرح کامل درخواست',
            'attachment' => UploadedFile::fake()->image('proof.jpg', 400, 200),
        ])->assertOk()->assertSee('کد رهگیری شکایت');

        $complaint = Complaint::query()->sole();
        $this->assertSame('local', $complaint->attachmentDisk());
        $this->assertStringStartsWith(Complaint::PRIVATE_ATTACHMENT_PREFIX, $complaint->attachment);
        $this->assertStringStartsWith('complaints/attachments/', $complaint->attachmentPath());
        Storage::disk('local')->assertExists($complaint->attachmentPath());
        Storage::disk('public')->assertMissing($complaint->attachmentPath());

        $this->get(route('admin.complaints.download', $complaint))
            ->assertRedirect(route('login'));

        $otherUser = $this->staffMember($this->union('other-complaint-union'));
        $this->actingAs($otherUser)->get(route('admin.complaints.download', $complaint))
            ->assertForbidden();

        $this->actingAs($this->signInAsSuperAdmin())
            ->get(route('admin.complaints.download', $complaint))
            ->assertOk()
            ->assertHeader('Cache-Control', 'private, no-store, max-age=0')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_admin_can_download_and_delete_legacy_public_attachment_without_moving_it(): void
    {
        $union = $this->union('legacy-complaint-union');
        $legacyPath = 'complaints/attachments/old-evidence.pdf';
        Storage::disk('public')->put($legacyPath, '%PDF-1.4 legacy test data');

        $complaint = $this->complaint($union, $legacyPath);
        $this->assertSame('public', $complaint->attachmentDisk());

        $this->signInAsSuperAdmin();
        $this->get(route('admin.complaints.download', $complaint))
            ->assertOk()
            ->assertHeader('Cache-Control', 'private, no-store, max-age=0');

        $this->delete(route('admin.complaints.destroy', $complaint))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($legacyPath);
        $this->assertDatabaseMissing('complaints', ['id' => $complaint->id]);
    }

    public function test_deleting_complaint_removes_private_attachment_only(): void
    {
        $union = $this->union('private-delete-union');
        $path = 'complaints/attachments/private-evidence.jpg';
        Storage::disk('local')->put($path, 'private file');
        Storage::disk('public')->put($path, 'different public legacy file');

        $complaint = $this->complaint($union, Complaint::privateAttachmentValue($path));
        $this->signInAsSuperAdmin();

        $this->delete(route('admin.complaints.destroy', $complaint))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_complaint_without_attachment_is_still_accepted(): void
    {
        $union = $this->union('no-attachment-union');
        $this->post(route('complaints.store'), [
            'union_id' => $union->id,
            'full_name' => 'شاکی بدون پیوست',
            'mobile' => '09123456789',
            'subject' => 'درخواست بدون فایل',
            'body' => 'شرح درخواست',
        ])->assertOk();

        $this->assertNull(Complaint::query()->sole()->attachment);
    }

    private function union(string $slug): GuildUnion
    {
        return GuildUnion::query()->create([
            'name' => 'اتحادیه آزمایشی',
            'title' => 'اتحادیه آزمایشی',
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function complaint(GuildUnion $union, ?string $attachment): Complaint
    {
        return Complaint::query()->create([
            'tracking_code' => 'CMP'.strtoupper(substr(md5((string) mt_rand()), 0, 9)),
            'union_id' => $union->id,
            'full_name' => 'شاکی آزمایشی',
            'mobile' => '09123456789',
            'subject' => 'موضوع شکایت',
            'body' => 'شرح شکایت',
            'status' => 'registered',
            'attachment' => $attachment,
        ]);
    }

    private function staffMember(GuildUnion $union): User
    {
        $role = Role::query()->create(['name' => 'complaints-viewer-test', 'label' => 'کاربر شکایات']);
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'complaints.view'],
            ['label' => 'مشاهده شکایات', 'group' => 'complaints']
        );
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['is_active' => true, 'union_id' => $union->id]);
        $user->roles()->attach($role);

        return $user;
    }
}
