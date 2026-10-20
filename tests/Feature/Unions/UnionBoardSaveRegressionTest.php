<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use App\Models\UnionMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnionBoardSaveRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function union(array $attributes = []): GuildUnion
    {
        return GuildUnion::create(array_replace(['title' => 'اتحادیه آهنگران', 'name' => 'اتحادیه آهنگران',
            'slug' => 'اتحادیه-آهنگران-گرگان', 'is_active' => true, 'members_enabled' => false,
            'settings' => ['show_board_members' => true]], $attributes));
    }

    private function payload(GuildUnion $union): array
    {
        return ['union_id' => $union->id, 'first_name' => 'عضو', 'last_name' => 'جدید',
            'position' => 'دبیر', 'sort_order' => 4, 'status' => 'active', 'is_active' => '1', 'board_editor' => '1'];
    }

    public function test_save_returns_to_existing_slug_edit_page_and_member_appears_without_saving_union(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union();
        $response = $this->post(route('admin.union_members.store'), $this->payload($union));
        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.unions.edit', $union).'#union-board-management');
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('عضو جدید');
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertSee('id="guild-board"', false)->assertSee('عضو جدید');
        $this->assertFalse($union->fresh()->members_enabled);
    }

    public function test_update_and_delete_return_to_slug_editor_and_change_public_board_immediately(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union();
        $member = UnionMember::create(['union_id' => $union->id, 'full_name' => 'نام اولیه', 'status' => 'active', 'is_active' => true]);
        $updated = $this->put(route('admin.union_members.update', $member), $this->payload($union));
        $updated->assertSessionHasNoErrors()->assertRedirect(route('admin.unions.edit', $union).'#union-board-management');
        $this->get($updated->headers->get('Location'))->assertOk()->assertSee('عضو جدید');
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertSee('عضو جدید');
        $deleted = $this->delete(route('admin.union_members.destroy', $member), ['board_editor' => '1']);
        $deleted->assertRedirect(route('admin.unions.edit', $union).'#union-board-management');
        $this->get($deleted->headers->get('Location'))->assertOk()->assertDontSee('عضو جدید');
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertDontSee('عضو جدید');
    }

    public function test_explicit_hide_is_respected_even_when_legacy_members_switch_is_enabled(): void
    {
        $union = $this->union(['members_enabled' => true, 'settings' => ['show_board_members' => false]]);
        UnionMember::create(['union_id' => $union->id, 'full_name' => 'عضو مخفی', 'status' => 'active', 'is_active' => true]);
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertDontSee('id="guild-board"', false)->assertDontSee('عضو مخفی');
    }

    public function test_explicit_show_is_not_blocked_by_disabled_legacy_switch(): void
    {
        $union = $this->union();
        UnionMember::create(['union_id' => $union->id, 'full_name' => 'عضو ثبت شده قبلی', 'status' => 'active', 'is_active' => true]);
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertSee('id="guild-board"', false)->assertSee('عضو ثبت شده قبلی');
    }

    public function test_legacy_visibility_is_used_when_current_setting_is_absent(): void
    {
        $union = $this->union(['members_enabled' => true, 'settings' => []]);
        UnionMember::create(['union_id' => $union->id, 'full_name' => 'عضو قدیمی', 'status' => 'active', 'is_active' => true]);
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertSee('عضو قدیمی');
        $union->update(['members_enabled' => false]);
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertDontSee('عضو قدیمی');
    }
}
