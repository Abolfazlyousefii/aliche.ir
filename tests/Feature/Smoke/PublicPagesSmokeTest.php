<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('publicPageProvider')]
    public function test_public_page_renders_without_an_exception(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public static function publicPageProvider(): array
    {
        return [
            'home' => ['/'],
            'posts' => ['/posts'],
            'guilds' => ['/guilds'],
            'daily news' => ['/daily-news'],
            'announcements' => ['/announcements'],
            'tourism' => ['/tourism'],
            'galleries' => ['/galleries'],
            'videos' => ['/videos'],
            'systems' => ['/systems'],
            'electronic services' => ['/electronic-services'],
            'commissions' => ['/commissions'],
            'contact' => ['/contact'],
            'complaint tracking' => ['/complaints/track'],
            'search' => ['/search'],
        ];
    }

    #[DataProvider('publicPaginationProvider')]
    public function test_public_paginated_pages_accept_page_two_with_a_query_string(string $path): void
    {
        $this->get($path.'?page=2&search=%D8%A2%D8%B2%D9%85%D9%88%D9%86')->assertOk();
    }

    public static function publicPaginationProvider(): array
    {
        return [
            'posts' => ['/posts'],
            'guilds' => ['/guilds'],
            'galleries' => ['/galleries'],
            'videos' => ['/videos'],
            'tourism' => ['/tourism'],
            'systems' => ['/systems'],
            'electronic services' => ['/electronic-services'],
            'commissions' => ['/commissions'],
        ];
    }
}
