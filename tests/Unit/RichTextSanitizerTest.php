<?php

namespace Tests\Unit;

use App\Services\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_unknown_wrapper_cannot_bypass_nested_element_and_attribute_sanitization(): void
    {
        $html = '<custom><img src="/storage/safe.jpg" onerror="alert(1)"><script>alert(2)</script><p onclick="alert(3)">متن امن</p></custom>';

        $clean = (new RichTextSanitizer())->sanitize($html);

        $this->assertIsString($clean);
        $this->assertStringContainsString('src="/storage/safe.jpg"', $clean);
        $this->assertStringContainsString('متن امن', $clean);
        $this->assertStringNotContainsString('<custom', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
    }

    public function test_blank_target_keeps_noopener_noreferrer(): void
    {
        $clean = (new RichTextSanitizer())->sanitize(
            '<a href="https://example.org" target="_blank">لینک</a>'
        );

        $this->assertStringContainsString('rel="noopener noreferrer"', (string) $clean);
    }
}
