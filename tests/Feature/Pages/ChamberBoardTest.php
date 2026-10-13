<?php

namespace Tests\Feature\Pages;

use App\Models\ChamberMember;
use App\Models\Page;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChamberBoardTest extends TestCase
{
    use RefreshDatabase;

    private function page(): Page
    {
        return Page::create(['title' => 'درباره اتاق', 'slug' => 'about-gorgan-guild-chamber', 'template' => 'default', 'status' => 'published', 'is_active' => true, 'body' => '<p>معرفی اتاق</p>']);
    }

    private function member(array $data = []): ChamberMember
    {
        return ChamberMember::create(array_replace(['first_name' => 'عضو', 'last_name' => 'آزمایشی', 'position' => 'عضو هیئت‌رئیسه', 'is_active' => true], $data));
    }

    private function payload(array $data = []): array
    {
        return array_replace(['first_name' => 'جدید', 'last_name' => 'آزمایشی', 'position' => 'عضو', 'is_active' => '1', 'is_president' => '0', 'sort_order' => 0], $data);
    }

    public function test_public_about_page_displays_president_first_and_ordered_active_members(): void
    {
        $page = $this->page();
        $this->member(['first_name' => 'دوم', 'sort_order' => 20]);
        $this->member(['first_name' => 'اول', 'sort_order' => 10, 'bio' => '<script>alert(1)</script>']);
        $this->member(['first_name' => 'رئیس', 'is_president' => true, 'sort_order' => 99]);
        $this->member(['first_name' => 'پنهان', 'is_active' => false]);
        $this->get(route('pages.show', $page->slug))->assertOk()
            ->assertSeeInOrder(['رئیس آزمایشی', 'اول آزمایشی', 'دوم آزمایشی'])
            ->assertDontSee('پنهان آزمایشی')->assertSee('chamber-member-dialog')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_create_more_than_five_members(): void
    {
        $this->signInAsSuperAdmin();
        for ($i = 0; $i < 7; $i++) {
            $this->post(route('admin.chamber_members.store'), $this->payload(['first_name' => 'عضو '.$i]))->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('chamber_members', 7);
    }

    public function test_second_active_president_is_rejected_and_current_president_can_be_edited(): void
    {
        $this->signInAsSuperAdmin();
        $president = $this->member(['is_president' => true]);
        $this->post(route('admin.chamber_members.store'), $this->payload(['is_president' => '1']))->assertSessionHasErrors('is_president');
        $this->patch(route('admin.chamber_members.update', $president), $this->payload(['is_president' => '1']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('chamber_members', 1);
    }

    public function test_deactivated_president_allows_replacement_but_cannot_be_reactivated_as_second(): void
    {
        $this->signInAsSuperAdmin();
        $old = $this->member(['is_president' => true, 'is_active' => false]);
        $this->post(route('admin.chamber_members.store'), $this->payload(['is_president' => '1']))->assertSessionHasNoErrors();
        $this->patch(route('admin.chamber_members.update', $old), $this->payload(['is_president' => '1']))->assertSessionHasErrors('is_president');
    }

    public function test_database_also_prevents_two_active_presidents(): void
    {
        $this->member(['is_president' => true]);
        $this->expectException(QueryException::class);
        $this->member(['is_president' => true]);
    }

    public function test_page_editor_contains_board_management_and_sidebar_shortcut(): void
    {
        $this->signInAsSuperAdmin();
        $page = $this->page();
        $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('id="chamber-board"', false)->assertSee('رییس هیئت اتاق اصناف');
        $this->get(route('admin.chamber_board.index'))->assertRedirect(route('admin.pages.edit', $page).'#chamber-board');
    }

    public function test_unrelated_pages_do_not_display_board_and_guest_cannot_manage_members(): void
    {
        $page = $this->page();
        $page->update(['slug' => 'other-page']);
        $this->member();
        $this->get(route('pages.show', $page->slug))->assertOk()->assertDontSee('chamber-board-page');
        $this->post(route('admin.chamber_members.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_board_template_keeps_working_on_a_custom_slug(): void
    {
        $page = $this->page();
        $page->update(['slug' => 'about-our-chamber', 'template' => 'chamber-about']);
        $this->member(['is_president' => true]);
        $this->get(route('pages.show', $page->slug))->assertOk()->assertSee('chamber-president');
    }

    public function test_migration_preserves_existing_member_data_and_can_be_reversed(): void
    {
        $migration = require database_path('migrations/2026_10_13_000001_add_president_to_chamber_members.php');
        $migration->down();
        $id = DB::table('chamber_members')->insertGetId(['first_name' => 'عضو قدیمی', 'last_name' => 'اتاق', 'position' => 'دبیر', 'photo' => 'existing.jpg', 'bio' => 'معرفی موجود', 'is_active' => true]);
        $migration->up();
        $member = ChamberMember::findOrFail($id);
        $this->assertFalse($member->is_president);
        $this->assertSame('existing.jpg', $member->photo);
        $this->assertSame('معرفی موجود', $member->bio);
        $migration->down();
        $this->assertDatabaseHas('chamber_members', ['id' => $id, 'first_name' => 'عضو قدیمی']);
        $migration->up();
    }
}
