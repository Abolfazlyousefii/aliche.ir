<?php

namespace Tests\Unit\Support;

use App\Support\PublicFileUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicFileUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('filesystems.disks.public.url', '/media-files');
    }

    public function test_plain_storage_path_uses_the_canonical_public_media_prefix(): void
    {
        $url = PublicFileUrl::make('posts/featured/example.jpg');

        $this->assertSame('/media-files/posts/featured/example.jpg', parse_url($url, PHP_URL_PATH));
        $this->assertStringNotContainsString('/media/posts/', $url);
    }

    #[DataProvider('localPathProvider')]
    public function test_local_media_variants_normalize_to_one_storage_path(string $input): void
    {
        $this->assertSame('posts/featured/example.jpg', PublicFileUrl::normalizeStoragePath($input));
        $this->assertSame('/media-files/posts/featured/example.jpg', parse_url(PublicFileUrl::make($input), PHP_URL_PATH));
    }

    public static function localPathProvider(): array
    {
        return [
            ['posts/featured/example.jpg'],
            ['/storage/posts/featured/example.jpg'],
            ['/media-files/posts/featured/example.jpg'],
            ['/media/posts/featured/example.jpg'],
            ['/uploaded-media/posts/featured/example.jpg'],
        ];
    }

    public function test_legacy_application_url_is_rewritten_but_external_url_is_untouched(): void
    {
        $legacy = 'https://aliche.ir/media/posts/featured/example.jpg';
        $external = 'https://example.org/image.jpg';

        $this->assertSame('/media-files/posts/featured/example.jpg', parse_url(PublicFileUrl::make($legacy), PHP_URL_PATH));
        $this->assertSame('posts/featured/example.jpg', PublicFileUrl::sameApplicationStoragePath($legacy));
        $this->assertSame($external, PublicFileUrl::make($external));
        $this->assertNull(PublicFileUrl::sameApplicationStoragePath($external));
    }
}
