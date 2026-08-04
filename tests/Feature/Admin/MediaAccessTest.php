<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_media_library_without_synced_permission_pivots(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'super-admin', 'label' => 'مدیرکل']);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('کتابخانه رسانه');
    }

    public function test_media_routes_use_action_specific_permissions(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'media-uploader', 'label' => 'آپلودکننده رسانه']);
        $permission = Permission::create(['name' => 'media.upload', 'label' => 'آپلود رسانه', 'group' => 'media']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.media.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.media.store'))
            ->assertSessionHasErrors('files');
    }

    public function test_storage_assets_fallback_redirects_to_public_assets(): void
    {
        $this->get('/storage/assets/img/asnaf-gorgan-default.jpg')
            ->assertRedirect(asset('assets/img/asnaf-gorgan-default.jpg'));
    }
}
