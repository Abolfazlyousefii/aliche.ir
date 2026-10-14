<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_logo_identity_and_quick_link_columns(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $response->assertSee('footer-brand-identity', false)
            ->assertSee('footer-logo-frame', false)
            ->assertSee('footer-brand-copy', false)
            ->assertSee('footer-quick-links', false)
            ->assertSee('data-footer-accordion="quick-links"', false)
            ->assertSee('footer-refinement.css', false)
            ->assertSee('دسترسی سریع')
            ->assertSee('اطلاعات تماس');
    }

    public function test_footer_is_shared_with_news_detail_pages(): void
    {
        $response = $this->get(route('posts.index'))->assertOk();
        $response->assertSee('footer-logo-frame', false)
            ->assertSee('footer-quick-links', false)
            ->assertSee('footer-refinement.css', false);
    }
}
