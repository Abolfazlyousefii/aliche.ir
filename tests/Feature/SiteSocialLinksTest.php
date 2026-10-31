<?php

namespace Tests\Feature;

use App\Services\SettingService;
use App\Support\SiteSocialLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_and_footer_show_saved_networks_with_distinct_brand_icons(): void
    {
        app(SettingService::class)->set('site.social_links', [
            ['platform' => 'bale', 'title' => 'کانال اتاق', 'url' => 'https://ble.ir/chamber-test'],
            ['platform' => 'telegram', 'title' => 'کانال اتاق', 'url' => 'https://t.me/chamber-test'],
            ['platform' => 'instagram', 'title' => 'صفحه اتاق', 'url' => 'https://instagram.com/chamber-test'],
        ]);
        app(SettingService::class)->set('footer.footer_social_links', []);

        $response = $this->get(route('contact.create'))->assertOk();
        foreach (['bale', 'telegram', 'instagram'] as $platform) {
            $this->assertSame(2, substr_count($response->getContent(), 'data-social-platform="'.$platform.'"'));
            $response->assertSee('data-brand-icon="'.$platform.'"', false);
        }
        $response->assertSee('شبکه‌های اجتماعی اتاق')->assertSee('بله')->assertSee('تلگرام');
    }

    public function test_legacy_links_are_inferred_and_footer_entries_are_preserved_without_duplicate_urls(): void
    {
        $settings = app(SettingService::class);
        $settings->set('site.social_links', ['بله' => 'https://ble.ir/chamber-test']);
        $settings->set('footer.footer_social_links', [
            ['platform' => 'custom', 'url' => 'https://ble.ir/chamber-test', 'title' => 'قدیمی'],
            ['url' => 'https://t.me/chamber-test', 'title' => 'کانال دوم'],
            ['url' => 'javascript:alert(1)', 'title' => 'unsafe'],
            ['url' => 'https://ble.ir.evil.example/test', 'title' => 'custom'],
        ]);

        $items = SiteSocialLinks::items($settings);
        $this->assertCount(3, $items);
        $this->assertSame(['bale', 'telegram', 'custom'], array_column($items, 'platform'));
    }
}
