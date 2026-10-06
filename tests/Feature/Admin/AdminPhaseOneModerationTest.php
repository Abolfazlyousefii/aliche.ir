<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class AdminPhaseOneModerationTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_a_queue_viewer_cannot_see_unrelated_pending_posts(): void
    {
        $post = $this->publishedPost([
            'slug' => 'private-pending-article',
            'title' => 'محتوای مخصوص تیم اخبار',
            'status' => 'pending',
        ]);

        $user = $this->withPermissions(['pending_approvals.view']);

        $this->actingAs($user)
            ->get(route('admin.pending_approvals.index'))
            ->assertOk()
            ->assertDontSee($post->title)
            ->assertDontSee('admin.pending_approvals.publish', false);
    }

    public function test_approver_can_reject_but_cannot_publish_without_publish_permission(): void
    {
        $post = $this->publishedPost([
            'slug' => 'pending-approval-news',
            'title' => 'خبر محدود به تایید',
            'status' => 'pending',
        ]);

        $user = $this->withPermissions(['pending_approvals.view', 'posts.approve']);
        $this->actingAs($user)
            ->get(route('admin.pending_approvals.index'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('admin/pending-approvals/posts/'.$post->id.'/reject', false)
            ->assertDontSee('admin/pending-approvals/posts/'.$post->id.'/publish', false);

        $this->actingAs($user)
            ->patch(route('admin.pending_approvals.publish', ['posts', $post->id]))
            ->assertForbidden();

        $this->assertSame('pending', $post->fresh()->status);
    }

    public function test_publisher_has_publish_action_without_reject_permission(): void
    {
        $post = $this->publishedPost([
            'slug' => 'publish-only-pending-news',
            'title' => 'خبر آماده انتشار',
            'status' => 'pending',
        ]);
        $user = $this->withPermissions(['pending_approvals.view', 'posts.publish']);

        $this->actingAs($user)
            ->get(route('admin.pending_approvals.index'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('admin/pending-approvals/posts/'.$post->id.'/publish', false)
            ->assertDontSee('admin/pending-approvals/posts/'.$post->id.'/reject', false);
    }


    public function test_publishing_announcement_from_queue_starts_immediately_and_shows_jalali_date(): void
    {
        $this->travelTo(now()->startOfMinute());
        $user = $this->withPermissions(['announcements.publish']);
        $announcement = Announcement::query()->create([
            'title' => 'اطلاعیه انتشار فوری',
            'slug' => 'announcement-queue-immediate',
            'status' => 'pending',
            'visibility' => 'public',
            'is_active' => true,
            'show_on_home' => true,
            'starts_at' => null,
            'published_at' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.pending_approvals.publish', ['announcements', $announcement->id]))
            ->assertRedirect();

        $announcement->refresh();
        $this->assertSame('published', $announcement->status);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $announcement->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame(now()->format('Y-m-d H:i:s'), $announcement->published_at->format('Y-m-d H:i:s'));

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee($announcement->title);

        $this->get(route('announcements.show', $announcement->slug))
            ->assertOk()
            ->assertSee(jalali_date($announcement->published_at))
            ->assertSee(jalali_datetime($announcement->starts_at));
    }

    public function test_jalali_schedule_is_saved_and_preserved_when_published_from_queue(): void
    {
        $this->travelTo(now()->startOfMinute());
        $user = $this->withPermissions(['announcements.create', 'announcements.view', 'announcements.publish']);
        $start = now()->addDays(2)->startOfMinute();
        $jalaliStart = jalali_datetime($start);

        $this->actingAs($user)->post(route('admin.announcements.store'), [
            'title' => 'اطلاعیه زمان‌بندی‌شده',
            'slug' => 'announcement-jalali-scheduled',
            'body' => 'متن اطلاعیه با تاریخ شمسی',
            'starts_at' => $jalaliStart,
            'status' => 'pending',
            'visibility' => 'public',
            'show_on_home' => '1',
            'is_important' => '0',
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $announcement = Announcement::query()->where('slug', 'announcement-jalali-scheduled')->firstOrFail();
        $this->assertSame($start->format('Y-m-d H:i:s'), $announcement->starts_at->format('Y-m-d H:i:s'));

        $this->get(route('admin.announcements.edit', $announcement))
            ->assertOk()
            ->assertSee($jalaliStart);

        $this->patch(route('admin.pending_approvals.publish', ['announcements', $announcement->id]))
            ->assertRedirect();

        $announcement->refresh();
        $this->assertSame('published', $announcement->status);
        $this->assertSame($start->format('Y-m-d H:i:s'), $announcement->starts_at->format('Y-m-d H:i:s'));

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertDontSee($announcement->title);

        $this->travelTo($start->copy()->addMinute());

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee($announcement->title);
    }

    public function test_direct_publish_still_fills_missing_announcement_start_date(): void
    {
        $this->travelTo(now()->startOfMinute());
        $user = $this->withPermissions(['announcements.publish']);
        $announcement = Announcement::query()->create([
            'title' => 'اطلاعیه انتشار مستقیم',
            'slug' => 'announcement-direct-immediate',
            'status' => 'approved',
            'visibility' => 'public',
            'is_active' => true,
            'starts_at' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.announcements.publish', $announcement))
            ->assertRedirect();

        $this->assertSame(
            now()->format('Y-m-d H:i:s'),
            $announcement->fresh()->starts_at->format('Y-m-d H:i:s')
        );
    }

    public function test_publishing_old_pending_post_from_approval_queue_uses_actual_time(): void
    {
        $this->travelTo(now()->startOfMinute());
        $user = $this->withPermissions(['posts.publish']);
        $post = $this->publishedPost([
            'title' => 'خبر تازه از صف انتشار',
            'slug' => 'publish-pending-old-date',
            'status' => 'pending',
            'published_at' => now()->subDays(5),
        ]);

        $this->actingAs($user)
            ->patch(route('admin.pending_approvals.publish', ['posts', $post->id]))
            ->assertRedirect();

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.index'))->assertOk()->assertSee($post->title);
    }

    public function test_publishing_scheduled_post_from_approval_queue_preserves_future_date(): void
    {
        $this->travelTo(now()->startOfMinute());
        $user = $this->withPermissions(['posts.publish']);
        $future = now()->addDays(2);
        $post = $this->publishedPost([
            'title' => 'خبر زمان‌بندی‌شده صف',
            'slug' => 'scheduled-post-approval-queue',
            'status' => 'pending',
            'published_at' => $future,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.pending_approvals.publish', ['posts', $post->id]))
            ->assertRedirect();

        $this->assertSame($future->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.index'))->assertOk()->assertDontSee($post->title);
    }

    private function withPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'moderator-test-'.uniqid(), 'label' => 'مدیر محتوا']);
        $ids = collect($permissions)->map(fn (string $name) => Permission::firstOrCreate(
            ['name' => $name],
            ['label' => $name, 'group' => explode('.', $name)[0]]
        )->id);

        $role->permissions()->sync($ids);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }
}
