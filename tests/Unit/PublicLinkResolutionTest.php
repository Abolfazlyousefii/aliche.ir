<?php

namespace Tests\Unit;

use App\Models\ElectronicService;
use App\Models\System;
use App\Services\LinkResolverService;
use Tests\TestCase;

class PublicLinkResolutionTest extends TestCase
{
    public function test_legacy_aliche_custom_url_is_normalized_to_current_app_url(): void
    {
        config(['app.url' => 'https://gorganasnaf.ir']);

        $resolved = app(LinkResolverService::class)->resolve(
            'custom',
            null,
            'https://aliche.ir/pages/about-gorgan-guild-chamber?source=menu#intro'
        );

        $this->assertSame(
            'https://gorganasnaf.ir/pages/about-gorgan-guild-chamber?source=menu#intro',
            $resolved
        );
    }

    public function test_electronic_service_placeholder_link_is_not_exposed_as_public_link(): void
    {
        $service = new ElectronicService([
            'link_type' => 'external',
            'link' => '#',
        ]);

        $this->assertNull($service->public_link);
    }

    public function test_electronic_service_exposes_valid_https_link(): void
    {
        $service = new ElectronicService([
            'link_type' => 'external',
            'link' => 'https://example.org/service',
        ]);

        $this->assertSame('https://example.org/service', $service->public_link);
    }

    public function test_system_placeholder_and_example_domains_are_not_public_links(): void
    {
        $placeholder = new System(['link' => '#']);
        $example = new System(['link' => 'https://example.com/system']);

        $this->assertNull($placeholder->public_link);
        $this->assertNull($example->public_link);
    }

    public function test_system_link_normalization_allows_http_https_and_internal_paths_only(): void
    {
        config(['app.url' => 'https://gorganasnaf.ir']);

        $this->assertSame('https://service.test/login', System::normalizePublicLink('https://service.test/login'));
        $this->assertSame('/internal/system', System::normalizePublicLink('/internal/system'));
        $this->assertSame('https://gorganasnaf.ir/internal/system', (new System(['link' => '/internal/system']))->public_link);

        $this->assertNull(System::normalizePublicLink('javascript:alert(1)'));
        $this->assertNull(System::normalizePublicLink('ftp://service.test/file'));
        $this->assertNull(System::normalizePublicLink('//evil.test/path'));
        $this->assertNull(System::normalizePublicLink('https://example.com/system'));
    }
}
