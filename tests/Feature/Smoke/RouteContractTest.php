<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RouteContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_critical_route_names_are_registered(): void
    {
        foreach ([
            'login',
            'home',
            'home.latest-news',
            'guilds.index',
            'guilds.show',
            'posts.index',
            'posts.show',
            'complaints.create',
            'complaints.store',
            'complaints.track',
            'complaints.track.result',
            'complaints.lookup',
            'media.public',
        ] as $name) {
            $this->assertTrue(Route::has($name), "Missing route [{$name}].");
        }
    }

    #[DataProvider('adminPageProvider')]
    public function test_guest_is_redirected_from_admin_pages_to_login(string $path): void
    {
        $this->get($path)->assertRedirect(route('login'));
    }

    public static function adminPageProvider(): array
    {
        return [
            ['/admin'],
            ['/admin/posts/create'],
            ['/admin/unions/create'],
        ];
    }

    public function test_complaint_tracking_page_renders_and_lookup_route_exists(): void
    {
        $this->assertTrue(Route::has('complaints.lookup'));
        $this->get('/complaints/track')->assertOk();
    }
}
