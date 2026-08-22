<?php

namespace Tests\Feature\Frontend;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_logo_is_also_used_by_footer_instead_of_legacy_footer_logo(): void
    {
        $headerLogo = SiteSetting::query()->create([
            'key' => 'header.desktop_logo',
            'group' => 'header',
            'value' => 'media/settings/site/test-logo.jpg',
        ]);
        SiteSetting::query()->create([
            'key' => 'footer.footer_logo',
            'group' => 'footer',
            'value' => 'assets/img/asnaf-footer-mark.svg',
        ]);
        Cache::forget('site_settings.all');

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($html, 'test-logo.jpg'));
        $this->assertStringNotContainsString('asnaf-footer-mark.svg', $html);

        $headerLogo->update(['value' => 'media/settings/site/updated-logo.jpg']);
        Cache::forget('site_settings.all');

        $updatedHtml = $this->get(route('home'))->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($updatedHtml, 'updated-logo.jpg'));
        $this->assertStringNotContainsString('test-logo.jpg', $updatedHtml);
        $this->assertStringNotContainsString('asnaf-footer-mark.svg', $updatedHtml);
    }

    public function test_footer_settings_form_does_not_offer_a_separate_logo_input(): void
    {
        $this->signInAsSuperAdmin();

        $this->get(route('admin.footer_settings.edit'))
            ->assertOk()
            ->assertDontSee('name="footer_logo"', false)
            ->assertDontSee('لوگوی فوتر');
    }
}
