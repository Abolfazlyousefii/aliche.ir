<?php

namespace Tests\Feature\Media;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyMediaRouteTest extends TestCase
{
    public function test_legacy_media_route_redirects_to_the_canonical_public_url(): void
    {
        config()->set('filesystems.disks.public.url', '/storage');

        $this->get('/media/posts/featured/example.jpg')
            ->assertRedirect('/storage/posts/featured/example.jpg');
    }

    public function test_missing_image_route_returns_the_central_placeholder_instead_of_404(): void
    {
        $this->get('/storage/missing/old-photo.jpg')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_legacy_prefix_serves_a_file_from_the_canonical_disk_with_long_cache_headers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/featured/legacy.jpg', 'legacy-image');

        $response = $this->get('/uploaded-media/posts/featured/legacy.jpg');

        $response->assertOk();
        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('immutable', (string) $response->headers->get('Cache-Control'));
    }

    public function test_non_image_missing_media_still_returns_404(): void
    {
        Storage::fake('public');

        $this->get('/media-files/missing/document.pdf')->assertNotFound();
    }
}
