<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationRegressionTest extends TestCase
{
    public function test_default_paginator_is_rtl_bootstrap_and_keeps_real_filtered_urls(): void
    {
        $paginator = new LengthAwarePaginator(
            range(11, 20),
            35,
            10,
            2,
            ['path' => 'https://example.test/posts', 'pageName' => 'page'],
        );
        $paginator->appends(['search' => 'خبر', 'status' => 'published']);

        $html = (string) $paginator->links();

        $this->assertStringContainsString('class="pagination-nav pagination-rtl"', $html);
        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('class="page-item active"', $html);
        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('href="https://example.test/posts?search=%D8%AE%D8%A8%D8%B1&amp;status=published"', $html);
        $this->assertStringContainsString('search=%D8%AE%D8%A8%D8%B1&amp;status=published&amp;page=3', $html);
        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringNotContainsString('javascript:', strtolower($html));
        $this->assertLessThan(strpos($html, '>۳<'), strpos($html, '>۱<'));
    }

    public function test_named_page_parameter_is_removed_only_from_the_first_page_url(): void
    {
        $paginator = new LengthAwarePaginator(
            range(7, 12),
            18,
            6,
            2,
            ['path' => 'https://example.test/', 'pageName' => 'news_page'],
        );
        $paginator->appends(['section' => 'latest']);

        $html = (string) $paginator->links();

        $this->assertStringContainsString('href="https://example.test?section=latest"', $html);
        $this->assertStringContainsString('section=latest&amp;news_page=3', $html);
        $this->assertStringNotContainsString('news_page=1', $html);
    }
}
