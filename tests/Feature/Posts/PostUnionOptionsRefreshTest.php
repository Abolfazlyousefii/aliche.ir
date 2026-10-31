<?php

namespace Tests\Feature\Posts;

use App\Models\{GuildUnion, Permission, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostUnionOptionsRefreshTest extends TestCase
{
    use RefreshDatabase;

    private function union(string $slug): GuildUnion
    {
        return GuildUnion::create(['name'=>'Legacy '.$slug,'title'=>'Current '.$slug,'slug'=>$slug,'is_active'=>true]);
    }

    private function editor(?int $unionId, string $roleName='editor',bool $active=true): User
    {
        $role=Role::firstOrCreate(['name'=>$roleName],['label'=>$roleName]);
        $permission=Permission::firstOrCreate(['name'=>'posts.edit'],['label'=>'Edit news','group'=>'posts']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user=User::factory()->create(['union_id'=>$unionId,'is_active'=>$active]);
        $user->roles()->attach($role);$this->actingAs($user);return $user;
    }

    public function test_refresh_returns_current_names_without_browser_caching(): void
    {
        $this->signInAsSuperAdmin();$union=$this->union('rename-options');
        $this->getJson('/admin/post-union-options')->assertOk()->assertJsonPath('data.0.text','Current rename-options');
        $union->update(['title'=>'Renamed while news form is open','name'=>'Old compatibility name']);
        $response=$this->getJson('/admin/post-union-options')->assertOk()->assertJsonPath('data.0.id',(string)$union->id)->assertJsonPath('data.0.text','Renamed while news form is open');
        $this->assertStringContainsString('no-store',(string)$response->headers->get('Cache-Control'));
        $this->assertSame(['id','text','is_active'],array_keys($response->json('data.0')));
    }

    public function test_union_editor_only_gets_the_assigned_union(): void
    {
        $own=$this->union('own-options');$other=$this->union('other-options');$this->editor($own->id,'union-expert');
        $this->getJson('/admin/post-union-options')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.id',(string)$own->id)->assertDontSee($other->title);
    }

    public function test_unassigned_union_expert_gets_no_union_names(): void
    {
        $this->union('hidden-options');$this->editor(null,'union-expert');
        $this->getJson('/admin/post-union-options')->assertOk()->assertExactJson(['data'=>[]]);
    }

    public function test_guest_and_user_without_news_permissions_cannot_list_names(): void
    {
        $this->getJson('/admin/post-union-options')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['is_active'=>true]));
        $this->getJson('/admin/post-union-options')->assertForbidden();
    }

    public function test_inactive_editor_is_rejected(): void
    {
        $this->editor(null,'editor',false);
        $this->getJson('/admin/post-union-options')->assertForbidden();
    }
}
