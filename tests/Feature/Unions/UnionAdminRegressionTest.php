<?php

namespace Tests\Feature\Unions;

use App\Models\Category;
use App\Models\GuildUnion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class UnionAdminRegressionTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_create_and_edit_forms_exclude_legacy_category_but_keep_union_type(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union(['slug' => 'union-form-edit']);

        foreach ([route('admin.unions.create'), route('admin.unions.edit', $union)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('name="category_id"', false)
                ->assertSee('name="union_type_id"', false);
        }
    }

    public function test_union_and_member_forms_expose_media_library_controls(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union(['slug' => 'union-media-controls']);

        $this->get(route('admin.unions.edit', $union))
            ->assertOk()
            ->assertSee('data-media-select-target="cover_image_media_id"', false)
            ->assertSee('data-media-select-target="logo_media_id"', false)
            ->assertSee('data-media-select-target="manager_image_media_id"', false)
            ->assertSee('data-media-select-target="price_list_image_media_id"', false);

        $this->get(route('admin.union_members.create'))
            ->assertOk()
            ->assertSee('data-media-select-target="image_media_id"', false);
    }

    public function test_union_edit_uses_controlled_icon_options_for_president_and_sections(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union([
            'slug' => 'union-icon-controls',
            'president_buttons' => [[
                'title' => 'تماس',
                'url' => 'tel:01712345678',
                'icon' => 'phone',
                'target' => '_self',
                'is_active' => true,
            ]],
        ]);

        $this->get(route('admin.unions.edit', $union))
            ->assertOk()
            ->assertSee('name="president_buttons[0][icon]"', false)
            ->assertSee('data-section="commissions"', false)
            ->assertSee('شماره ترتیبی')
            ->assertSee('نرخ‌نامه');
    }

    public function test_update_without_category_id_preserves_legacy_category_value(): void
    {
        $this->signInAsSuperAdmin();
        $category = Category::query()->create([
            'title' => 'دسته قدیمی اتحادیه',
            'slug' => 'legacy-union-category',
            'type' => 'union',
            'is_active' => true,
        ]);
        $union = $this->union([
            'slug' => 'legacy-category-union',
            'category_id' => $category->id,
        ]);

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => 'اتحادیه ویرایش‌شده',
            'slug' => $union->slug,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($category->id, $union->refresh()->category_id);
    }

    public function test_non_super_admin_is_scoped_to_assigned_union(): void
    {
        $ownUnion = $this->union(['slug' => 'assigned-union']);
        $otherUnion = $this->union(['slug' => 'other-union']);

        $role = Role::query()->create([
            'name' => 'union-manager-test',
            'label' => 'مدیر اتحادیه تست',
        ]);

        $permissions = collect(['unions.view', 'unions.edit', 'unions.create'])
            ->map(fn (string $name) => Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => 'unions']
            ));

        $role->permissions()->sync($permissions->pluck('id'));

        $user = User::factory()->create([
            'is_active' => true,
            'union_id' => $ownUnion->id,
        ]);
        $user->roles()->sync([$role->id]);
        $this->actingAs($user);

        $this->get(route('admin.unions.index'))
            ->assertOk()
            ->assertSee($ownUnion->title)
            ->assertDontSee($otherUnion->title);

        $this->get(route('admin.unions.edit', $ownUnion))->assertOk();
        $this->get(route('admin.unions.edit', $otherUnion))->assertForbidden();
        $this->get(route('admin.unions.create'))->assertForbidden();
    }

    public function test_manager_fields_are_persisted_on_store_and_update(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.unions.store'), $this->unionPayload([
            'title' => 'اتحادیه مدیر پویا',
            'slug' => 'dynamic-manager-union',
            'manager_name' => 'مدیر نخست',
            'manager_position' => 'رئیس اتحادیه',
            'manager_description' => 'معرفی کوتاه مدیر نخست',
        ]))->assertSessionHasNoErrors();

        $union = GuildUnion::query()->where('slug', 'dynamic-manager-union')->firstOrFail();
        $this->assertSame('مدیر نخست', $union->manager_name);
        $this->assertSame('رئیس اتحادیه', $union->manager_position);
        $this->assertSame('معرفی کوتاه مدیر نخست', $union->manager_description);

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'manager_name' => 'مدیر دوم',
            'manager_position' => 'سرپرست اتحادیه',
            'manager_description' => 'معرفی به‌روزشده مدیر دوم',
        ]))->assertSessionHasNoErrors();

        $union->refresh();
        $this->assertSame('مدیر دوم', $union->manager_name);
        $this->assertSame('سرپرست اتحادیه', $union->manager_position);
        $this->assertSame('معرفی به‌روزشده مدیر دوم', $union->manager_description);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه تست مدیریت',
            'title' => 'اتحادیه تست مدیریت',
            'slug' => 'admin-union-'.uniqid(),
            'is_active' => true,
        ], $overrides));
    }
}
