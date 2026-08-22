<?php

namespace Tests\Feature\Media;

use Tests\TestCase;

class LegacyMediaRouteTest extends TestCase
{
    public function test_legacy_media_route_redirects_to_the_canonical_public_url(): void
    {
        config()->set('filesystems.disks.public.url', '/media-files');

        $this->get('/media/posts/featured/example.jpg')
            ->assertRedirect('/media-files/posts/featured/example.jpg');
    }
}
