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
}
