<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSectionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsSuperAdmin();
        // Migrations seed the default homepage sections; isolate our scenarios.
        HomeSection::query()->delete();
    }

    private function section(string $key, bool $active = true, int $sort = 10): HomeSection
    {
        return HomeSection::query()->create([
            'key' => $key,
            'title' => 'تست '.$key,
            'sort_order' => $sort,
            'is_active' => $active,
        ]);
    }

    public function test_admin_sort_page_has_individual_switches_and_preserves_inactive_rows(): void
    {
        $active = $this->section('hero_slider', true, 10);
        $inactive = $this->section('unions', false, 20);

        $response = $this->get(route('admin.home_sections.index'))->assertOk()
            ->assertSee('ذخیره ترتیب و وضعیت')
            ->assertSee('data-home-visible', false)
            ->assertSee('home-section-disabled', false)
            ->assertSee('role="switch"', false)
            ->assertSee('statuses['.$active->id.']', false)
            ->assertSee('statuses['.$inactive->id.']', false);

        $html = $response->getContent();
        $this->assertSame(2, substr_count($html, '<tr data-section-row'));
        $this->assertStringContainsString('value="0"', $html);
    }

    public function test_sort_and_visibility_are_saved_together_without_deleting_section_data(): void
    {
        $first = $this->section('electronic_services', true, 10);
        $second = $this->section('unions', false, 20);

        $this->post(route('admin.home_sections.sort'), [
            'sections' => [$second->id, $first->id],
            'statuses' => [(string) $first->id => '0', (string) $second->id => '1'],
        ])->assertRedirect(route('admin.home_sections.index'));

        $this->assertDatabaseHas('home_sections', ['id' => $first->id, 'is_active' => 0, 'sort_order' => 20]);
        $this->assertDatabaseHas('home_sections', ['id' => $second->id, 'is_active' => 1, 'sort_order' => 10]);
        $this->assertSame('تست electronic_services', $first->fresh()->title);
    }

    public function test_missing_states_or_incomplete_sort_do_not_mass_deactivate_sections(): void
    {
        $a = $this->section('hero_slider', true, 10);
        $b = $this->section('quick_menu', true, 20);

        $this->post(route('admin.home_sections.sort'), [
            'sections' => [$a->id, $b->id],
            'statuses' => [(string) $a->id => '0'],
        ])->assertSessionHasErrors('statuses');

        $this->assertTrue($a->fresh()->is_active);
        $this->assertTrue($b->fresh()->is_active);

        $this->post(route('admin.home_sections.sort'), [
            'sections' => [$a->id],
            'statuses' => [(string) $a->id => '0', (string) $b->id => '0'],
        ])->assertSessionHasErrors('sections');

        $this->assertSame(10, $a->fresh()->sort_order);
        $this->assertSame(20, $b->fresh()->sort_order);
    }

    public function test_legacy_order_only_post_keeps_existing_statuses_unchanged(): void
    {
        $a = $this->section('unions', true, 10);
        $b = $this->section('videos', false, 20);

        $this->post(route('admin.home_sections.sort'), [
            'sections' => [$b->id, $a->id],
        ])->assertRedirect(route('admin.home_sections.index'));

        $this->assertTrue($a->fresh()->is_active);
        $this->assertFalse($b->fresh()->is_active);
        $this->assertSame(10, $b->fresh()->sort_order);
    }

    public function test_homepage_does_not_render_disabled_independent_sections(): void
    {
        $this->section('unions', false);
        $this->section('electronic_services', false, 20);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringNotContainsString('<section class="home-union-news"', $html);
        $this->assertStringNotContainsString('<section class="site-container howto-section', $html);
        $this->assertStringContainsString('class="hero-section site-container"', $html);
    }

    public function test_hero_slider_and_quick_menu_can_be_disabled_independently(): void
    {
        $slider = $this->section('hero_slider', false);
        $quick = $this->section('quick_menu', true, 20);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('hero-grid-no-slider', $html);
        $this->assertStringContainsString('class="quick-menu"', $html);
        $this->assertStringNotContainsString('class="hero-slider swiper"', $html);

        $slider->update(['is_active' => true]);
        $quick->update(['is_active' => false]);
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('hero-grid-no-quick', $html);
        $this->assertStringNotContainsString('class="quick-menu"', $html);
        $this->assertStringContainsString('class="hero-slider swiper"', $html);

        $slider->update(['is_active' => false]);
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringNotContainsString('<section class="hero-section', $html);
    }

    public function test_videos_and_galleries_tabs_independently_respect_status(): void
    {
        $video = $this->section('videos', false);
        $gallery = $this->section('galleries', true, 20);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('data-tab-panel="media-image"', $html);
        $this->assertStringNotContainsString('data-tab-panel="media-video"', $html);

        $video->update(['is_active' => true]);
        $gallery->update(['is_active' => false]);
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('data-tab-panel="media-video"', $html);
        $this->assertStringNotContainsString('data-tab-panel="media-image"', $html);

        $video->update(['is_active' => false]);
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringNotContainsString('<section class="multimedia-section"', $html);
    }
}
