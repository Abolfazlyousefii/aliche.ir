<?php

namespace Tests\Feature\Admin;

use App\Models\GuildUnion;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_union_create_screen_exposes_six_accessible_sections_with_original_controls(): void
    {
        $this->signInAsSuperAdmin();

        $response = $this->get(route('admin.unions.create'));
        $response->assertOk()
            ->assertSee('data-admin-workspace-kind="union"', false)
            ->assertSee('form-workspace.css')
            ->assertSee('form-workspace.js')
            ->assertSee('data-admin-workspace-submit', false)
            ->assertSee('data-admin-form-workspace', false)
            ->assertSee('admin-workspace-savebar', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="news_mode"', false)
            ->assertSee('name="price_list_mode"', false)
            ->assertSee('name="manager_name"', false)
            ->assertSee('name="executive_name"', false)
            ->assertSee('name="settings[show_manager]"', false)
            ->assertSee('data-section="commissions"', false)
            ->assertSee('data-section="rules"', false)
            ->assertSee('data-section="prices"', false);

        foreach (['identity', 'people', 'contact', 'news', 'sections', 'settings'] as $tab) {
            $response->assertSee('data-admin-workspace-tab="'.$tab.'"', false)
                ->assertSee('data-admin-workspace-pane="'.$tab.'"', false)
                ->assertSee('aria-controls="admin-form-pane-'.$tab.'"', false)
                ->assertSee('aria-labelledby="admin-form-tab-'.$tab.'"', false);
        }

        $this->assertSame(6, substr_count($response->getContent(), 'data-admin-workspace-pane="'));
        $this->assertSame(1, substr_count($response->getContent(), 'name="title"'));
    }

    public function test_union_edit_preserves_all_fields_and_one_global_submit_action(): void
    {
        $this->signInAsSuperAdmin();
        $union = GuildUnion::query()->create([
            'title' => 'اتحادیه آزمایشی',
            'name' => 'اتحادیه آزمایشی',
            'slug' => 'phase3-editor-union',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.unions.edit', $union));
        $response->assertOk()
            ->assertSee('اتحادیه آزمایشی')
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="president_buttons[', false)
            ->assertSee('name="selected_posts[]"', false)
            ->assertSee('name="settings[', false)
            ->assertSee('name="meta_title"', false)
            ->assertSee('name="social_links[', false);

        $this->assertSame(1, substr_count($response->getContent(), 'data-admin-workspace-submit'));
    }

    public function test_post_create_and_edit_render_five_accessible_sections(): void
    {
        $this->signInAsSuperAdmin();
        $create = $this->get(route('admin.posts.create'));
        $create->assertOk()
            ->assertSee('data-admin-workspace-kind="post"', false)
            ->assertSee('name="body"', false)
            ->assertSee('name="published_at"', false)
            ->assertSee('name="featured_image"', false)
            ->assertSee('name="gallery_images[]"', false)
            ->assertSee('name="meta_keywords[]"', false)
            ->assertSee('form-workspace.js');

        foreach (['identity', 'publication', 'article', 'media', 'seo'] as $tab) {
            $create->assertSee('data-admin-workspace-tab="'.$tab.'"', false)
                ->assertSee('data-admin-workspace-pane="'.$tab.'"', false);
        }

        $this->assertSame(5, substr_count($create->getContent(), 'data-admin-workspace-pane="'));

        $post = Post::query()->create([
            'title' => 'خبر تست فرم',
            'slug' => 'phase3-post-editor',
            'type' => 'news',
            'status' => 'draft',
            'is_active' => true,
        ]);
        $this->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee('خبر تست فرم')
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('data-admin-workspace-submit', false);
    }

    public function test_invalid_union_submission_shows_validation_summary_and_retains_input(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.unions.store'), [
            'title' => '',
            'manager_name' => 'نام واردشده رئیس',
        ])->assertSessionHasErrors('title');

        $this->get(route('admin.unions.create'))
            ->assertOk()
            ->assertSee('data-admin-workspace-error="title"', false)
            ->assertSee('نام واردشده رئیس')
            ->assertSee('role="alert"', false);
    }

    public function test_invalid_news_submission_renders_tab_aware_errors_without_losing_text(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.posts.store'), [
            'title' => '',
            'type' => 'news',
            'status' => 'draft',
            'excerpt' => 'خلاصه آزمایشی که باید باقی بماند',
        ])->assertSessionHasErrors('title');

        $this->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('data-admin-workspace-error="title"', false)
            ->assertSee('خلاصه آزمایشی که باید باقی بماند')
            ->assertSee('role="alert"', false);
    }
}
