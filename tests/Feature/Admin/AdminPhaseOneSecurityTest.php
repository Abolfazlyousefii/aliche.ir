<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\ChamberMember;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UnionType;
use App\Models\User;
use App\Support\AdminCategoryAccess;
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminPhaseOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_without_permissions_cannot_modify_global_reference_data(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $category = Category::create(['title' => 'دسته خبر', 'slug' => 'secure-news', 'type' => 'news', 'is_active' => true]);
        $unionType = UnionType::create(['title' => 'نوع اتحادیه تست', 'slug' => 'secure-union-type', 'is_active' => true]);
        $member = ChamberMember::create(['first_name' => 'عضو', 'last_name' => 'تست', 'position' => 'عضو', 'is_active' => true]);

        $this->actingAs($user);
        $this->get(route('admin.categories.index'))->assertForbidden();
        $this->get(route('admin.categories.create', ['type' => 'news']))->assertForbidden();
        $this->post(route('admin.categories.store'), ['type' => 'news', 'title' => 'جدید'])->assertForbidden();
        $this->get(route('admin.categories.edit', $category))->assertForbidden();
        $this->put(route('admin.categories.update', $category), ['type' => 'news', 'title' => 'تغییر'])->assertForbidden();
        $this->delete(route('admin.categories.destroy', $category))->assertForbidden();

        $this->get(route('admin.union-types.index'))->assertForbidden();
        $this->get(route('admin.union-types.create'))->assertForbidden();
        $this->post(route('admin.union-types.store'), [])->assertForbidden();
        $this->get(route('admin.union-types.edit', $unionType))->assertForbidden();
        $this->put(route('admin.union-types.update', $unionType), [])->assertForbidden();
        $this->delete(route('admin.union-types.destroy', $unionType))->assertForbidden();

        $this->get(route('admin.chamber_members.index'))->assertForbidden();
        $this->get(route('admin.chamber_members.create'))->assertForbidden();
        $this->post(route('admin.chamber_members.store'), [])->assertForbidden();
        $this->get(route('admin.chamber_members.edit', $member))->assertForbidden();
        $this->put(route('admin.chamber_members.update', $member), [])->assertForbidden();
        $this->delete(route('admin.chamber_members.destroy', $member))->assertForbidden();
        $this->postJson(route('admin.rich_text.upload'), [])->assertForbidden();

        // Private inboxes have row-level protections and stay usable.
        $this->get(route('admin.messages.inbox'))->assertOk();
    }

    public function test_categories_are_limited_to_their_owning_module_and_action(): void
    {
        $user = $this->withPermissions(['posts.view', 'posts.create', 'posts.edit']);
        $news = Category::create(['title' => 'دسته خبری مجاز', 'slug' => 'news-allowed', 'type' => 'news', 'is_active' => true]);
        $tourism = Category::create(['title' => 'دسته گردشگری محرمانه', 'slug' => 'tourism-restricted', 'type' => 'tourism', 'is_active' => true]);

        $this->actingAs($user);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('دسته خبری مجاز')
            ->assertDontSee('دسته گردشگری محرمانه');

        $this->get(route('admin.categories.index', ['type' => 'tourism']))->assertForbidden();
        $this->get(route('admin.categories.create', ['type' => 'tourism']))->assertForbidden();
        $this->get(route('admin.categories.edit', $tourism))->assertForbidden();
        $this->delete(route('admin.categories.destroy', $news))->assertForbidden();

        $this->post(route('admin.categories.store'), [
            'type' => 'tourism', 'title' => 'دسته غیرمجاز', 'is_active' => 1,
        ])->assertForbidden();

        $this->put(route('admin.categories.update', $news), [
            'type' => 'tourism', 'title' => 'انتقال غیرمجاز', 'is_active' => 1,
        ])->assertForbidden();

        $this->put(route('admin.categories.update', $news), [
            'type' => 'news', 'title' => 'دسته ویرایش شده', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('دسته ویرایش شده', $news->fresh()->title);
    }

    public function test_union_expert_cannot_edit_chamber_board_or_union_types(): void
    {
        $user = $this->withPermissions(['dashboard.view', 'unions.view', 'unions.edit', 'union_members.view']);
        $this->actingAs($user);

        $this->get(route('admin.union-types.index'))->assertForbidden();
        $this->get(route('admin.chamber_members.index'))->assertForbidden();

        $visible = AdminNavigation::searchableLinks($user);
        $titles = collect($visible)->pluck('title')->all();

        $this->assertContains('اتحادیه‌ها', $titles);
        $this->assertNotContains('انواع اتحادیه', $titles);
        $this->assertNotContains('اعضای اتاق اصناف', $titles);
    }

    public function test_quick_navigation_is_permission_scoped_and_every_link_is_registered(): void
    {
        $user = $this->withPermissions(['posts.view']);
        $visible = AdminNavigation::searchableLinks($user);
        $titles = collect($visible)->pluck('title')->all();

        $this->assertContains('اخبار و مقاله‌ها', $titles);
        $this->assertContains('دسته‌بندی اخبار', $titles);
        $this->assertNotContains('مکان‌های گردشگری', $titles);
        $this->assertNotContains('کاربران پنل', $titles);
        $this->assertNotContains('انواع اتحادیه', $titles);

        $this->assertTrue(AdminCategoryAccess::can($user, 'news', 'view'));
        $this->assertFalse(AdminCategoryAccess::can($user, 'tourism', 'view'));

        foreach (AdminNavigation::groups() as $group) {
            foreach (($group['children'] ?: [$group]) as $link) {
                $this->assertTrue(Route::has($link['route']), 'Unknown admin navigation route: '.$link['route']);
            }
        }

        $this->assertCount(8, AdminNavigation::groups());
    }

    public function test_super_admin_keeps_all_navigation_and_reference_management(): void
    {
        $user = $this->signInAsSuperAdmin();

        $this->assertCount(8, AdminNavigation::forUser($user));
        $this->get(route('admin.union-types.index'))->assertOk();
        $this->get(route('admin.chamber_members.index'))->assertOk();
        $this->get(route('admin.categories.index'))->assertOk();
    }

    public function test_media_upload_permission_is_required_for_rich_editor(): void
    {
        $user = $this->withPermissions(['media.upload']);
        $this->actingAs($user)
            ->postJson(route('admin.rich_text.upload'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    private function withPermissions(array $names): User
    {
        $role = Role::create(['name' => 'security-test-'.uniqid(), 'label' => 'کاربر محدود']);
        $permissionIds = collect($names)->map(function (string $name) {
            return Permission::firstOrCreate(['name' => $name], ['label' => $name, 'group' => explode('.', $name)[0]])->id;
        })->all();

        $role->permissions()->sync($permissionIds);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }
}
