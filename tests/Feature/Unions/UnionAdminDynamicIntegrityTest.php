<?php

namespace Tests\Feature\Unions;

use App\Models\GuildUnion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class UnionAdminDynamicIntegrityTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_manual_selections_follow_admin_order_and_survive_mode_switches(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union();
        $ownFirst = $this->publishedPost(['union_id' => $union->id, 'slug' => 'manual-own-first']);
        $ownSecond = $this->publishedPost(['union_id' => $union->id, 'slug' => 'manual-own-second']);
        $other = $this->union(['slug' => 'integrity-other-union']);
        $foreign = $this->publishedPost(['union_id' => $other->id, 'slug' => 'manual-foreign-news']);

        // Legacy cross-union selections are retained in storage but must not
        // be offered as new choices or shown on the public union page.
        $union->selectedPosts()->attach($foreign->id, ['sort_order' => 1]);

        $this->get(route('admin.unions.edit', $union))
            ->assertOk()
            ->assertSee('manual-own-first')
            ->assertDontSee('manual-foreign-news');

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'news_mode' => 'manual',
            'selected_posts' => [$ownSecond->id, $ownFirst->id],
        ]))->assertSessionHasNoErrors();

        $selection = $union->fresh()->selectedPosts()->get();
        $this->assertSame(
            [$foreign->id, $ownSecond->id, $ownFirst->id],
            $selection->pluck('id')->all()
        );

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'news_mode' => 'auto',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(
            [$foreign->id, $ownSecond->id, $ownFirst->id],
            $union->fresh()->selectedPosts()->pluck('posts.id')->all()
        );

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'news_mode' => 'manual',
            'selected_posts' => [$foreign->id],
        ]))->assertSessionHasErrors('selected_posts.0');
    }

    public function test_failed_nested_write_rolls_back_main_union_changes(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union(['title' => 'عنوان سالم اولیه']);

        // SQLite in-memory only: simulate a real DB error *after* updating the
        // main union so this test exercises the transaction rollback.
        DB::statement("CREATE TRIGGER fail_union_commission BEFORE INSERT ON union_commissions BEGIN SELECT RAISE(ABORT, 'test nested failure'); END");

        $failed = false;
        $this->withoutExceptionHandling();
        try {
            $this->put(route('admin.unions.update', $union), $this->unionPayload([
                'title' => 'عنوان نباید باقی بماند',
                'slug' => $union->slug,
                'related' => [
                    'commissions' => [[
                        'title' => 'کمیسیون ناموفق',
                        'sort_order' => 0,
                        'is_active' => 1,
                    ]],
                ],
            ]));
        } catch (QueryException $exception) {
            $failed = true;
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS fail_union_commission');
            $this->withExceptionHandling();
        }

        $this->assertTrue($failed, 'Simulated nested database failure must be triggered.');
        $this->assertSame('عنوان سالم اولیه', $union->fresh()->title);
        $this->assertDatabaseMissing('union_commissions', [
            'union_id' => $union->id,
            'title' => 'کمیسیون ناموفق',
        ]);
    }

    public function test_admin_exposes_remove_buttons_for_new_dynamic_rows(): void
    {
        $this->signInAsSuperAdmin();
        $this->get(route('admin.unions.edit', $this->union()))
            ->assertOk()
            ->assertSee('data-remove-unsaved-row', false)
            ->assertSee('data-section="commission-tasks"', false)
            ->assertSee('data-section="rules"', false)
            ->assertSee('data-section="prices"', false);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه کنترل داده',
            'title' => 'اتحادیه کنترل داده',
            'slug' => 'union-integrity-'.uniqid(),
            'news_enabled' => true,
            'is_active' => true,
        ], $overrides));
    }
}
