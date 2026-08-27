<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FrontendAjaxAssetsTest extends TestCase
{
    public function test_ajax_core_contains_progressive_enhancement_handlers(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $script = file_get_contents($projectRoot.'/public/assets/js/ajax-core.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('[data-latest-news][data-endpoint]', $script);
        $this->assertStringContainsString('[data-guilds-directory]', $script);
        $this->assertStringContainsString('[data-guild-type-link][href]', $script);
        $this->assertStringContainsString('[data-pagination] a[href]', $script);
        $this->assertStringContainsString('window.history.pushState', $script);
        $this->assertStringContainsString("window.addEventListener('popstate'", $script);
        $this->assertStringContainsString("'X-Requested-With': 'XMLHttpRequest'", $script);
        $this->assertStringNotContainsString('eval(', $script);
        $this->assertStringNotContainsString('new Function(', $script);
    }

    public function test_frontend_layout_loads_versioned_scripts_and_script_stack(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $scriptsPartial = file_get_contents($projectRoot.'/resources/views/frontend/partials/scripts.blade.php');
        $layout = file_get_contents($projectRoot.'/resources/views/frontend/layouts/app.blade.php');

        $this->assertIsString($scriptsPartial);
        $this->assertStringContainsString("public_path('assets/js/main.js')", $scriptsPartial);
        $this->assertStringContainsString("public_path('assets/js/ajax-core.js')", $scriptsPartial);
        $this->assertStringContainsString("asset('assets/js/main.js')", $scriptsPartial);
        $this->assertStringContainsString("asset('assets/js/ajax-core.js')", $scriptsPartial);

        $this->assertIsString($layout);
        $this->assertStringContainsString("@include('frontend.partials.scripts')", $layout);
        $this->assertStringContainsString("@stack('scripts')", $layout);
    }
}
