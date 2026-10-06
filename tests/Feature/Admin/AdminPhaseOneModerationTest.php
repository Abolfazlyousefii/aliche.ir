<?php

namespace Tests\Feature\Admin;

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
