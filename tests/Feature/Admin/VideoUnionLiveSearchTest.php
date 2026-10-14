<?php

namespace Tests\Feature\Admin;

use App\Models\GuildUnion;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoUnionLiveSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsSuperAdmin();
    }

    private function union(): GuildUnion
    {
        return GuildUnion::query()->create([
            'name' => 'اتحادیه آرایشگران مردانه گرگان',
            'title' => 'اتحادیه آرایشگران مردانه گرگان',
            'slug' => 'union-live-search-video',
            'is_active' => true,
        ]);
    }

    public function test_create_has_searchable_union_component_and_preserves_native_select(): void
    {
        $union = $this->union();

        $this->get(route('admin.videos.create'))
            ->assertOk()
            ->assertSee('data-video-union-picker', false)
            ->assertSee('data-video-union-native', false)
            ->assertSee('id="union_id"', false)
            ->assertSee('name="union_id"', false)
            ->assertSee('ویدیوی عمومی (بدون اتحادیه)')
            ->assertSee($union->display_title)
            ->assertSee('video-union-search.js', false)
            ->assertSee('video-union-search.css', false);
    }

    public function test_edit_has_selected_union_and_search_assets(): void
    {
        $union = $this->union();
        $video = Video::query()->create([
            'title' => 'گزارش اتحادیه',
            'slug' => 'union-live-search-video-edit',
            'video_type' => 'upload',
            'status' => 'draft',
            'is_active' => true,
            'union_id' => $union->id,
        ]);

        $response = $this->get(route('admin.videos.edit', $video))->assertOk()
            ->assertSee('data-video-union-picker', false)
            ->assertSee('video-union-search.js', false);

        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<option value="'.preg_quote((string) $union->id, '/').'" selected\b[^>]*>/',
            $html
        );
    }

    public function test_old_union_value_is_retained_after_validation_failure(): void
    {
        $union = $this->union();

        $response = $this->withSession(['_old_input' => ['union_id' => (string) $union->id]])
            ->get(route('admin.videos.create'))->assertOk();

        $this->assertMatchesRegularExpression(
            '/<option value="'.preg_quote((string) $union->id, '/').'" selected\b[^>]*>/',
            $response->getContent()
        );
    }
}
