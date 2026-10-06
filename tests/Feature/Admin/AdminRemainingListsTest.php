<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRemainingListsTest extends TestCase
{
    use RefreshDatabase;

    private const FILTER_LISTS = [
        'advertisements', 'advertisement_positions', 'galleries', 'videos',
        'systems', 'contact_messages', 'roles', 'permissions',
        'commissions', 'congratulation_messages',
    ];

    public function test_all_ten_remaining_searchable_lists_render_shared_filters_and_mobile_assets(): void
    {
        $this->signInAsSuperAdmin();

        foreach (self::FILTER_LISTS as $name) {
            $response = $this->get(route('admin.'.$name.'.index'));

            $response->assertOk()
                ->assertSee('admin-list-filter-card', false)
                ->assertSee('admin-list-filter-form', false)
                ->assertSee('data-admin-list-table', false)
                ->assertSee('admin-list-responsive-table', false)
                ->assertSee('list-workspace.css')
                ->assertSee('list-workspace.js')
                ->assertSee('name="search"', false)
                ->assertSee('method="GET"', false);
        }
    }

    public function test_existing_filter_names_and_values_survive_round_trip(): void
    {
        $this->signInAsSuperAdmin();

        $cases = [
            ['advertisements', ['status' => 'scheduled', 'search' => 'تست زمان‌بندی'], ['status', 'search']],
            ['advertisement_positions', ['status' => 'inactive'], ['status']],
            ['galleries', ['status' => 'draft'], ['status', 'union_id']],
            ['videos', ['video_type' => 'upload'], ['video_type', 'status', 'union_id']],
            ['systems', ['status' => 'draft'], ['status', 'category_id']],
            ['contact_messages', ['read_status' => 'unread'], ['read_status']],
            ['commissions', ['status' => 'draft'], ['status']],
            ['congratulation_messages', ['status' => 'draft'], ['status', 'union_id']],
        ];

        foreach ($cases as [$name, $parameters, $expectedNames]) {
            $response = $this->get(route('admin.'.$name.'.index', $parameters));
            $response->assertOk();
            foreach ($expectedNames as $fieldName) {
                $response->assertSee('name="'.$fieldName.'"', false);
            }
            foreach ($parameters as $value) {
                $response->assertSee('value="'.$value.'"', false);
            }
        }
    }

    public function test_galleries_keep_the_original_drag_sorting_markup(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertSee('id="gallery-sortable"', false)
            ->assertSee('data-admin-list-sortable="true"', false)
            ->assertSee(route('admin.galleries.sort'));
    }

    public function test_chamber_members_list_hides_mutations_for_read_only_account(): void
    {
        $user = $this->userWithAbilities(['chamber_members.view']);
        $this->actingAs($user);

        $this->get(route('admin.chamber_members.index'))
            ->assertOk()
            ->assertSee('data-admin-list-table', false)
            ->assertSee('list-workspace.js')
            ->assertDontSee(route('admin.chamber_members.create'))
            ->assertDontSee('>حذف</button>', false);
    }

    public function test_role_and_permission_lists_do_not_show_mutation_buttons_to_read_only_accounts(): void
    {
        $user = $this->userWithAbilities(['roles.view', 'permissions.view']);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertOk()
            ->assertDontSee(route('admin.roles.create'))
            ->assertDontSee('>حذف</button>', false)
            ->assertDontSee('>ویرایش</a>', false);

        $this->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertDontSee(route('admin.permissions.create'))
            ->assertDontSee('>حذف</button>', false)
            ->assertDontSee('>ویرایش</a>', false);
    }

    public function test_read_only_media_library_hides_upload_and_mutations_but_offers_copy_and_view_switch(): void
    {
        $user = $this->userWithAbilities(['media.view']);
        $this->actingAs($user);

        $this->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('data-admin-media-view="grid"', false)
            ->assertSee('data-admin-media-view="list"', false)
            ->assertSee('data-admin-media-grid', false)
            ->assertSee('admin-wp-media-readonly', false)
            ->assertSee('list-workspace.js')
            ->assertDontSee('action="'.route('admin.media.store').'"', false)
            ->assertDontSee('data-media-dropzone', false);

        $this->assertFalse($user->hasPermission('media.upload'));
        $this->assertFalse($user->hasPermission('media.delete'));
    }

    public function test_super_admin_retains_media_upload_and_access_to_all_list_pages(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee(route('admin.media.store'))
            ->assertSee('data-media-dropzone', false);

        $this->get(route('admin.chamber_members.index'))
            ->assertOk()
            ->assertSee(route('admin.chamber_members.create'));

        $this->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee(route('admin.roles.create'));
    }

    private function userWithAbilities(array $abilities): User
    {
        $role = Role::create(['name' => 'phase5-'.uniqid(), 'label' => 'کاربر فقط‌خواندنی']);
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
