<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UnionMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class UnionMinimalBoardTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_board_is_inside_hero_and_shows_only_own_active_members_in_order(): void
    {
        $own = $this->union('board-visible-one', ['members_enabled' => true, 'manager_name' => 'رئیس اصلی']);
        $other = $this->union('board-visible-two', ['members_enabled' => true]);

        foreach ([['آخرین عضو', 20, 'active', true, $own->id],
            ['اولین عضو', 5, 'active', true, $own->id],
            ['عضو غیرفعال', 1, 'active', false, $own->id],
            ['عضو تعلیق', 2, 'suspended', true, $own->id],
            ['عضو اتحادیه دیگر', 0, 'active', true, $other->id]] as [$name, $sort, $status, $active, $unionId]) {
            UnionMember::query()->create(['union_id' => $unionId, 'full_name' => $name,
                'position' => 'عضو هیئت‌مدیره', 'sort_order' => $sort, 'status' => $status, 'is_active' => $active]);
        }

        $response = $this->get(route('guilds.show', $own->slug));
        $response->assertOk()
            ->assertSee('guild-profile-page--hero-refresh', false)
            ->assertSeeInOrder(['guild-profile-hero__inner--v4', 'id="guild-board"', 'اولین عضو', 'آخرین عضو'], false)
            ->assertSame(1, substr_count($response->getContent(), 'id="guild-board"'))
            ->assertSee('data-guild-board-slider', false)
            ->assertSee('swiper-wrapper', false)
            ->assertDontSee('guild-profile-member__business', false)
            ->assertDontSee('عضو غیرفعال')->assertDontSee('عضو تعلیق')
            ->assertDontSee('عضو اتحادیه دیگر')
            ->assertDontSee('guild-profile-member__business', false);
    }

    public function test_covered_hero_puts_board_before_news_and_leaves_news_layout_intact(): void
    {
        $union = $this->union('hero-only-preserves-layout', [
            'manager_name' => 'رئیس اصلی', 'manager_position' => 'رئیس اتحادیه',
            'news_enabled' => true, 'members_enabled' => true,
            'settings' => ['show_news' => true, 'show_board_members' => true],
        ]);
        $this->publishedPost(['slug' => 'hero-only-news', 'title' => 'خبر صفحه اتحادیه',
            'type' => 'news', 'union_id' => $union->id]);
        UnionMember::query()->create(['union_id' => $union->id,
            'full_name' => 'عضو اصلی', 'position' => 'دبیر', 'status' => 'active', 'is_active' => true]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('guild-profile-page--hero-refresh', false)
            ->assertSee('assets/css/guild-minimal.css', false)
            ->assertSee('guild-profile-quickbar', false)
            ->assertSeeInOrder(['id="guild-manager"', 'id="guild-board"', 'id="guild-news"'], false)
            ->assertSee('guild-profile-news-feature', false)
            ->assertSame(1, substr_count($this->get(route('guilds.show', $union->slug))->getContent(), 'id="guild-board"'))
            ->assertSee('خبر صفحه اتحادیه')
            ->assertSee('عضو اصلی');

        $css = file_get_contents(base_path('public/assets/css/guild-minimal.css'));
        $this->assertNotFalse($css);
        $this->assertStringContainsString('guild-profile-hero__inner--v4', $css);
        $this->assertStringNotContainsString('.guild-profile-section', $css);
        $this->assertStringNotContainsString('.guild-profile-quickbar', $css);
        $this->assertStringNotContainsString('.guild-profile-members', $css);
        $this->assertSame($css, file_get_contents(base_path('public_html/assets/css/guild-minimal.css')));
    }

    public function test_union_cover_president_data_and_swiper_are_dynamic_per_union(): void
    {
        $own = $this->union('cover-board-own', [
            'title' => 'اتحادیه اصلی تست', 'members_enabled' => true,
            'cover_image' => 'unions/own-cover.jpg',
            'manager_name' => 'پریسا ساعی',
            'manager_position' => 'رئیس هیئت‌مدیره',
            'manager_image' => 'unions/president-own.jpg',
        ]);
        $other = $this->union('cover-board-other', [
            'title' => 'اتحادیه دیگر تست', 'members_enabled' => true,
            'cover_image' => 'unions/other-cover.jpg',
            'manager_name' => 'رئیس دیگر',
        ]);

        foreach ([
            [$own->id, 'مهتاب بختیاری', 1],
            [$own->id, 'شهرام همتی', 2],
            [$other->id, 'عضو دیگر', 0],
        ] as [$unionId, $name, $order]) {
            UnionMember::query()->create([
                'union_id' => $unionId, 'full_name' => $name,
                'position' => 'عضو هیئت‌مدیره', 'sort_order' => $order,
                'status' => 'active', 'is_active' => true,
            ]);
        }

        $this->get(route('guilds.show', $own->slug))
            ->assertOk()
            ->assertSee('guild-profile-hero__backdrop', false)
            ->assertSee('unions/own-cover.jpg')
            ->assertDontSee('unions/other-cover.jpg')
            ->assertSee('اتحادیه اصلی تست')
            ->assertSee('پریسا ساعی')
            ->assertSee('رئیس هیئت‌مدیره')
            ->assertSee('unions/president-own.jpg')
            ->assertSeeInOrder(['id="guild-board"', 'مهتاب بختیاری', 'شهرام همتی'], false)
            ->assertDontSee('عضو دیگر')
            ->assertDontSee('رئیس دیگر')
            ->assertSee('new Swiper(slider', false);
    }

    public function test_missing_cover_and_portraits_render_neutral_fallbacks_without_extra_sections(): void
    {
        $union = $this->union('missing-union-photos', [
            'members_enabled' => true,
            'manager_name' => 'رئیس بدون تصویر',
            'cover_image' => null,
            'manager_image' => null,
        ]);
        UnionMember::query()->create([
            'union_id' => $union->id, 'full_name' => 'عضو بدون تصویر',
            'position' => 'عضو', 'status' => 'active', 'is_active' => true,
        ]);

        $this->get(route('guilds.show', $union->slug))
            ->assertOk()
            ->assertSee('رئیس بدون تصویر')
            ->assertSee('عضو بدون تصویر')
            ->assertSee('تصویر ثبت نشده')
            ->assertSee('guild-profile-hero__overlay', false)
            ->assertDontSee('guild-profile-hero__backdrop', false);
    }

    public function test_no_board_is_rendered_without_real_active_members(): void
    {
        $union = $this->union('board-empty', ['members_enabled' => true]);
        $this->get(route('guilds.show', $union->slug))->assertOk()->assertDontSee('id="guild-board"', false);
    }

    public function test_union_edit_has_separate_board_management_only_for_own_members(): void
    {
        $this->signInAsSuperAdmin();
        $own = $this->union('board-admin-own');
        $other = $this->union('board-admin-other');
        UnionMember::query()->create(['union_id' => $own->id, 'full_name' => 'مدیر همین اتحادیه',
            'status' => 'active', 'is_active' => true]);
        UnionMember::query()->create(['union_id' => $other->id, 'full_name' => 'مدیر اتحادیه دیگر',
            'status' => 'active', 'is_active' => true]);

        $this->get(route('admin.unions.edit', $own))
            ->assertOk()
            ->assertSee('id="union-board-management"', false)
            ->assertSee('مدیر همین اتحادیه')
            ->assertDontSee('مدیر اتحادیه دیگر')
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="image_media_id"', false);
    }

    public function test_board_member_can_be_added_with_names_order_and_media_library_photo(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union('board-admin-create');
        $image = Media::query()->create(['file_name' => 'portrait.jpg', 'original_name' => 'portrait.jpg',
            'path' => 'media/union/portrait.jpg', 'disk' => 'public', 'mime_type' => 'image/jpeg',
            'extension' => 'jpg', 'size' => 1000]);

        $this->post(route('admin.union_members.store'), [
            'union_id' => $union->id, 'first_name' => 'لیلا', 'last_name' => 'کاظمی',
            'position' => 'دبیر', 'sort_order' => 8, 'status' => 'active',
            'is_active' => '1', 'image_media_id' => $image->id, 'board_editor' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.unions.edit', $union->id).'#union-board-management');

        $this->assertDatabaseHas('union_members', ['union_id' => $union->id,
            'first_name' => 'لیلا', 'last_name' => 'کاظمی', 'full_name' => 'لیلا کاظمی',
            'position' => 'دبیر', 'sort_order' => 8, 'image' => $image->path]);
    }

    public function test_board_editor_can_upload_photo_update_order_and_disable_only_selected_member(): void
    {
        $this->signInAsSuperAdmin();
        Storage::fake('public');
        $own = $this->union('board-upload-own', ['members_enabled' => true]);
        $other = $this->union('board-upload-other', ['members_enabled' => true]);
        $member = UnionMember::query()->create([
            'union_id' => $own->id, 'full_name' => 'نام قدیمی', 'position' => 'عضو',
            'sort_order' => 3, 'status' => 'active', 'is_active' => true,
        ]);
        $foreign = UnionMember::query()->create([
            'union_id' => $other->id, 'full_name' => 'عضو دیگر', 'position' => 'دبیر',
            'sort_order' => 4, 'status' => 'active', 'is_active' => true,
        ]);

        $this->put(route('admin.union_members.update', $member), [
            'union_id' => $own->id, 'first_name' => 'نرگس', 'last_name' => 'رضایی',
            'position' => 'نایب‌رئیس', 'sort_order' => 1, 'status' => 'active',
            'is_active' => '0', 'board_editor' => '1',
            'image' => UploadedFile::fake()->image('board-portrait.jpg', 140, 170),
        ])->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame('نرگس رضایی', $member->full_name);
        $this->assertSame(1, $member->sort_order);
        $this->assertFalse($member->is_active);
        $this->assertNotEmpty($member->image);
        Storage::disk('public')->assertExists($member->image);
        $this->assertSame('عضو دیگر', $foreign->refresh()->full_name);
        $this->assertSame($other->id, $foreign->union_id);
        $this->get(route('guilds.show', $own->slug))->assertDontSee('نرگس رضایی');
    }

    public function test_board_editor_cannot_move_a_member_into_another_union(): void
    {
        $own = $this->union('board-owner');
        $other = $this->union('board-foreign');
        $role = Role::query()->create(['name' => 'board-editor-test', 'label' => 'ویرایشگر']);
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'union_members.edit'], ['label' => 'ویرایش اعضا', 'group' => 'union_members']);
        $role->permissions()->sync([$permission->id]);
        $user = User::factory()->create(['is_active' => true, 'union_id' => $own->id]);
        $user->roles()->sync([$role->id]);
        $member = UnionMember::query()->create(['union_id' => $own->id,
            'full_name' => 'عضو حفظ مالکیت', 'status' => 'active', 'is_active' => true]);

        $this->actingAs($user)->put(route('admin.union_members.update', $member), [
            'union_id' => $other->id, 'first_name' => 'نام', 'last_name' => 'جدید',
            'position' => 'دبیر', 'sort_order' => 0, 'status' => 'active', 'is_active' => '1',
        ])->assertSessionHasErrors('union_id');

        $this->assertDatabaseHas('union_members', ['id' => $member->id,
            'union_id' => $own->id, 'full_name' => 'عضو حفظ مالکیت']);
    }

    private function union(string $slug, array $fields = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه تست', 'title' => 'اتحادیه تست', 'slug' => $slug, 'is_active' => true,
        ], $fields));
    }
}
