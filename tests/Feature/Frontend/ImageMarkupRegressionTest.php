<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageMarkupRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_image_tags_have_async_decoding_and_an_explicit_loading_strategy(): void
    {
        $checked = 0;

        foreach (File::allFiles(resource_path('views/frontend')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);

            foreach ($lines as $line => $tag) {
                if (! str_contains($tag, '<img ')) {
                    continue;
                }

                if (str_contains($tag, 'class="lightbox-img"') && str_contains($tag, 'src=""')) {
                    continue;
                }

                $checked++;
                $location = $file->getPathname().':'.($line + 1);
                $this->assertStringContainsString('decoding=', $tag, "Missing decoding attribute in {$location}: {$tag}");
                $this->assertStringContainsString('loading=', $tag, "Missing loading strategy in {$location}: {$tag}");
            }
        }

        $this->assertGreaterThan(20, $checked, 'Too few frontend image tags were inspected.');
    }

    public function test_primary_hero_is_eager_and_high_priority_not_lazy(): void
    {
        $contents = file_get_contents(resource_path('views/frontend/home/sections/hero_slider.blade.php'));

        $this->assertStringContainsString("loading=\"{{ \$loop->first ? 'eager' : 'lazy' }}\"", $contents);
        $this->assertStringContainsString('fetchpriority="high"', $contents);
        $this->assertStringContainsString('loading="eager"', $contents);
    }

    public function test_key_empty_frontend_pages_stay_within_query_and_html_size_budgets(): void
    {
        foreach (['/', '/posts', '/guilds', '/galleries'] as $path) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $response = $this->get($path)->assertOk();
            $queries = count(DB::getQueryLog());
            DB::disableQueryLog();
            $bytes = strlen($response->getContent());
            $images = substr_count($response->getContent(), '<img');

            $this->assertLessThanOrEqual(60, $queries, "{$path} executed {$queries} queries.");
            $this->assertLessThanOrEqual(1_500_000, $bytes, "{$path} returned {$bytes} bytes of HTML.");
            $this->assertLessThanOrEqual(100, $images, "{$path} rendered {$images} image requests before lazy loading.");
        }
    }
}
