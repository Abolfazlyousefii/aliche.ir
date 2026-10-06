<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders_actionable_cards_quick_links_and_shared_shell(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('class="admin-dashboard-v2"', false)
            ->assertSee('class="admin-dashboard-intro"', false)
            ->assertSee('class="admin-dashboard-metrics"', false)
            ->assertSee('اتحادیه‌های فعال')
            ->assertSee('خبر جدید')
            ->assertSee('نیازمند رسیدگی')
            ->assertSee('admin-ui-v2.css')
            ->assertSee('id="adminMainContent"', false)
            ->assertSee('admin-skip-link', false)
            ->assertSee('data-admin-sidebar-close', false)
            ->assertSee('data-admin-quick-search', false);
    }

    public function test_user_with_only_dashboard_permission_sees_no_restricted_counts_or_actions(): void
    {
        $user = $this->withPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('admin-dashboard-v2')
            ->assertSee('مورد فوری ثبت نشده است')
            ->assertDontSee('اتحادیه‌های فعال')
            ->assertDontSee('شکایت‌های باز')
            ->assertDontSee('پیام‌های تماس جدید')
            ->assertDontSee('گیرندگان پیامک موفق')
            ->assertDontSee('وضعیت سامانه')
            ->assertDontSee('خبر جدید');
    }

    public function test_union_expert_sees_only_own_module_metrics_and_no_global_shortcuts(): void
    {
        $user = $this->withPermissions(['dashboard.view', 'unions.view', 'union_members.view']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('اتحادیه‌های فعال')
            ->assertSee('اعضای فعال')
            ->assertDontSee('شکایت‌های باز')
            ->assertDontSee('پیام‌های تماس جدید')
            ->assertDontSee('خبر جدید')
            ->assertDontSee('اتحادیه جدید');
    }

    public function test_editor_can_open_direct_creation_shortcut_with_specific_permission(): void
    {
        $user = $this->withPermissions(['dashboard.view', 'posts.create']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('خبر جدید')
            ->assertSee(route('admin.posts.create'))
            ->assertDontSee('اتحادیه جدید')
            ->assertDontSee('صفحه جدید');
    }

    private function withPermissions(array $abilities): User
    {
        $role = Role::create(['name' => 'phase2-'.uniqid(), 'label' => 'کاربر تست']);
        $ids = collect($abilities)->map(fn (string $ability) => Permission::firstOrCreate(
            ['name' => $ability],
            ['label' => $ability, 'group' => explode('.', $ability)[0]]
        )->id)->all();

        $role->permissions()->sync($ids);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }
}
