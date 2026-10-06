<?php

namespace Tests\Feature\Admin;

use App\Models\GuildUnion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private const LIST_ROUTES = [
        'admin.unions.index',
        'admin.union_members.index',
        'admin.complaints.index',
        'admin.tourism.index',
        'admin.electronic_services.index',
        'admin.announcements.index',
        'admin.pages.index',
        'admin.users.index',
    ];

    public function test_eight_admin_lists_render_shared_get_filters_and_mobile_progressive_enhancement(): void
    {
        $this->signInAsSuperAdmin();

        foreach (self::LIST_ROUTES as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk()
                ->assertSee('admin-list-filter-card', false)
                ->assertSee('admin-list-filter-form', false)
                ->assertSee('data-admin-list-table', false)
                ->assertSee('admin-list-responsive-table', false)
                ->assertSee('list-workspace.css')
                ->assertSee('list-workspace.js')
                ->assertSee('method="GET"', false)
                ->assertSee('name="search"', false)
                ->assertSee('فیلترهای بیشتر');
        }
    }

    public function test_union_list_preserves_keyword_and_status_query_semantics_and_total_count(): void
    {
        $this->signInAsSuperAdmin();

        GuildUnion::query()->create([
            'title' => 'اتحادیه تست فعال بهار',
            'name' => 'اتحادیه تست فعال بهار',
            'slug' => 'admin-list-spring',
            'is_active' => true,
        ]);
        GuildUnion::query()->create([
            'title' => 'اتحادیه تست غیرفعال پاییز',
            'name' => 'اتحادیه تست غیرفعال پاییز',
            'slug' => 'admin-list-autumn',
            'is_active' => false,
        ]);

        $this->get(route('admin.unions.index', ['search' => 'تست', 'status' => 'active']))
            ->assertOk()
            ->assertSee('اتحادیه تست فعال بهار')
            ->assertDontSee('اتحادیه تست غیرفعال پاییز')
            ->assertSee('value="تست"', false)
            ->assertSee('value="active" selected', false)
            ->assertSee('فیلتر فعال')
            ->assertSee('نتیجه');

        $this->get(route('admin.unions.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('اتحادیه تست غیرفعال پاییز')
            ->assertDontSee('اتحادیه تست فعال بهار');
    }

    public function test_read_only_union_manager_cannot_see_create_edit_or_delete_actions(): void
    {
        $union = GuildUnion::query()->create([
            'title' => 'اتحادیه فقط مطالعه',
            'name' => 'اتحادیه فقط مطالعه',
            'slug' => 'admin-list-reader',
            'is_active' => true,
        ]);
        $user = $this->withPermissions(['unions.view'], $union->id);
        $this->actingAs($user);

        $this->get(route('admin.unions.index'))
            ->assertOk()
            ->assertSee('اتحادیه فقط مطالعه')
            ->assertDontSee(route('admin.unions.create'))
            ->assertDontSee(route('admin.unions.edit', $union))
            ->assertDontSee(route('admin.unions.destroy', $union))
            ->assertSee(route('admin.unions.show', $union));
    }

    public function test_user_list_read_only_view_does_not_offer_mutations(): void
    {
        $user = $this->withPermissions(['users.view']);
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee(route('admin.users.create'))
            ->assertDontSee('>حذف</button>', false);
    }

    private function withPermissions(array $abilities, ?int $unionId = null): User
    {
        $role = Role::create(['name' => 'phase4-'.uniqid(), 'label' => 'کاربر محدود فهرست']);
        $ids = collect($abilities)->map(fn (string $ability) => Permission::firstOrCreate(
            ['name' => $ability],
            ['label' => $ability, 'group' => explode('.', $ability)[0]]
        )->id)->all();
        $role->permissions()->sync($ids);

        $user = User::factory()->create(['is_active' => true, 'union_id' => $unionId]);
        $user->roles()->attach($role);

        return $user;
    }
}
