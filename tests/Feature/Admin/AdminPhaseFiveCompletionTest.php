<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Commission;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\UnionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPhaseFiveCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_admin_indexes_keep_search_filters_and_mobile_assets(): void
    {
        $this->signInAsSuperAdmin();

        foreach ([
            'admin.categories.index',
            'admin.union-types.index',
            'admin.menus.index',
            'admin.messages.index',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('admin-list-filter-form', false)
                ->assertSee('data-admin-list-table', false)
                ->assertSee('name="search"', false)
                ->assertSee('list-workspace.css')
                ->assertSee('list-workspace.js');
        }

        $commission = Commission::query()->create([
            'title' => 'کمیسیون تست',
            'slug' => 'phase5-completion-commission',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $this->get(route('admin.commissions.sessions.index', [$commission, 'search' => 'جلسه']))
            ->assertOk()
            ->assertSee('data-admin-list-table', false)
            ->assertSee('value="جلسه"', false)
            ->assertSee('name="status"', false);

        foreach (['admin.messages.inbox', 'admin.messages.sent', 'admin.sms.index'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('data-admin-list-table', false)
                ->assertSee('list-workspace.js');
        }
    }

    public function test_category_search_keeps_module_scope_and_prevents_leaking_other_categories(): void
    {
        Category::query()->create(['type' => 'news', 'title' => 'اخبار مجاز پاییز', 'slug' => 'phase5-news']);
        Category::query()->create(['type' => 'tourism', 'title' => 'اطلاعات گردشگری پاییز', 'slug' => 'phase5-tourism']);

        $user = $this->withPermissions(['posts.view']);
        $this->actingAs($user);

        $this->get(route('admin.categories.index', ['search' => 'پاییز']))
            ->assertOk()
            ->assertSee('اخبار مجاز پاییز')
            ->assertDontSee('اطلاعات گردشگری پاییز')
            ->assertSee('value="پاییز"', false);

        $this->get(route('admin.categories.index', ['type' => 'tourism', 'search' => 'پاییز']))
            ->assertForbidden();
    }

    public function test_union_type_and_menu_filters_return_expected_results(): void
    {
        $this->signInAsSuperAdmin();

        UnionType::query()->create(['title' => 'نوع صنعتی فعال', 'slug' => 'phase5-active-industry', 'is_active' => true]);
        UnionType::query()->create(['title' => 'نوع صنعتی غیرفعال', 'slug' => 'phase5-inactive-industry', 'is_active' => false]);

        $this->get(route('admin.union-types.index', ['search' => 'صنعتی', 'status' => 'active']))
            ->assertOk()
            ->assertSee('نوع صنعتی فعال')
            ->assertDontSee('نوع صنعتی غیرفعال');

        Menu::query()->create(['title' => 'منوی پاییز فعال', 'location' => 'main', 'is_active' => true]);
        Menu::query()->create(['title' => 'منوی پاییز غیرفعال', 'location' => 'footer', 'is_active' => false]);

        $this->get(route('admin.menus.index', ['search' => 'پاییز', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('منوی پاییز غیرفعال')
            ->assertDontSee('منوی پاییز فعال');
    }

    public function test_readonly_menu_and_union_type_accounts_cannot_access_action_controls(): void
    {
        $menu = Menu::query()->create(['title' => 'منوی ویژه', 'location' => 'main', 'is_active' => true]);
        $type = UnionType::query()->create(['title' => 'نوع اتحادیه آزمایشی', 'slug' => 'phase5-readonly-type', 'is_active' => true]);

        $user = $this->withPermissions(['menus.view', 'union_types.view']);
        $this->actingAs($user);

        $this->get(route('admin.menus.index'))
            ->assertOk()
            ->assertSee('منوی ویژه')
            ->assertDontSee(route('admin.menus.create'))
            ->assertDontSee('action="'.route('admin.menus.destroy', $menu).'"', false)
            ->assertDontSee('>ویرایش</a>', false);

        $this->get(route('admin.union-types.index'))
            ->assertOk()
            ->assertSee('نوع اتحادیه آزمایشی')
            ->assertDontSee(route('admin.union-types.create'))
            ->assertDontSee('action="'.route('admin.union-types.destroy', $type).'"', false)
            ->assertDontSee('>ویرایش</a>', false);
    }

    public function test_home_section_reordering_is_editable_with_keyboard_and_disabled_for_view_only(): void
    {
        HomeSection::query()->updateOrCreate(['key' => 'hero_slider'], ['title' => 'اسلایدر صفحه اول', 'sort_order' => 10, 'is_active' => true]);

        $viewer = $this->withPermissions(['home_sections.view']);
        $this->actingAs($viewer)
            ->get(route('admin.home_sections.index'))
            ->assertOk()
            ->assertSee('data-home-sorting-enabled="false"', false)
            ->assertDontSee('data-home-move="up"', false)
            ->assertDontSee('draggable="true"', false);

        $editor = $this->withPermissions(['home_sections.view', 'home_sections.edit']);
        $this->actingAs($editor)
            ->get(route('admin.home_sections.index'))
            ->assertOk()
            ->assertSee('data-home-sorting-enabled="true"', false)
            ->assertSee('data-home-move="up"', false)
            ->assertSee('data-home-move="down"', false)
            ->assertSee('role="status"', false)
            ->assertSee('draggable="true"', false);
    }

    public function test_pending_queue_text_filter_only_operates_on_authorized_content(): void
    {
        Post::query()->create([
            'title' => 'خبر آزمون رسیدگی پاییز',
            'slug' => 'phase5-pending-visible',
            'type' => 'news',
            'status' => 'pending',
            'is_active' => true,
        ]);

        $viewer = $this->withPermissions(['pending_approvals.view']);
        $this->actingAs($viewer)
            ->get(route('admin.pending_approvals.index', ['search' => 'آزمون']))
            ->assertOk()
            ->assertDontSee('خبر آزمون رسیدگی پاییز');

        $moderator = $this->withPermissions(['pending_approvals.view', 'posts.approve']);
        $this->actingAs($moderator)
            ->get(route('admin.pending_approvals.index', ['search' => 'آزمون']))
            ->assertOk()
            ->assertSee('خبر آزمون رسیدگی پاییز')
            ->assertSee('name="type"', false)
            ->assertSee('name="search"', false)
            ->assertSee('admin-list-table', false);

        $this->get(route('admin.pending_approvals.index', ['search' => 'عبارت ناموجود']))
            ->assertOk()
            ->assertDontSee('خبر آزمون رسیدگی پاییز');
    }

    private function withPermissions(array $abilities): User
    {
        $role = Role::create(['name' => 'phase5-completion-'.uniqid(), 'label' => 'کاربر تست فاز پنجم']);
        $ids = collect($abilities)->map(fn (string $name) => Permission::firstOrCreate(
            ['name' => $name],
            ['label' => $name, 'group' => explode('.', $name)[0]]
        )->id)->all();
        $role->permissions()->sync($ids);

        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }
}
