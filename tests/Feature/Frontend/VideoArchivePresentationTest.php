<?php

namespace Tests\Feature\Frontend;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoArchivePresentationTest extends TestCase
{
    use RefreshDatabase;

    private function publishVideo(int $index, string $type = 'upload'): Video
    {
        return Video::query()->create([
            'title' => 'عنوان ویدئوی آزمایشی '.$index,
            'slug' => 'video-archive-demo-'.$index,
            'description' => 'گزارش تصویری درباره فعالیت‌های اتاق اصناف و کسب‌وکارها',
            'video_type' => $type,
            'video_file' => $type === 'upload' ? 'videos/example-'.$index.'.mp4' : null,
            'aparat_url' => $type === 'aparat' ? 'https://www.aparat.com/v/AbC123' : null,
            'cover_image' => 'media/videos/example-'.$index.'.jpg',
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->subDays($index),
            'sort_order' => $index,
        ]);
    }

    public function test_archive_uses_legible_title_cards_without_technical_source_badges(): void
    {
        $this->publishVideo(1);
        $this->publishVideo(2, 'aparat');

        $response = $this->get(route('videos.index'))->assertOk();
        $response->assertSee('video-archive-grid', false)
            ->assertSee('video-archive-card__title', false)
            ->assertSee('video-archive-card--featured', false)
            ->assertSee('video-archive-search', false)
            ->assertSee('video-archive.css', false)
            ->assertSee('عنوان ویدئوی آزمایشی 1')
            ->assertSee('عنوان ویدئوی آزمایشی 2')
            ->assertDontSee('آپلود مستقیم')
            ->assertDontSee('لینک آپارات');

        $this->assertSame(2, substr_count($response->getContent(), 'data-video-archive-card'));
        $this->assertSame(1, substr_count($response->getContent(), 'video-archive-card--featured'));
    }

    public function test_archive_search_and_second_page_retain_headlines_but_only_first_page_is_featured(): void
    {
        foreach (range(1, 14) as $number) {
            $this->publishVideo($number);
        }

        $second = $this->get(route('videos.index', ['page' => 2]))
            ->assertOk()
            ->assertDontSee('video-archive-card--featured', false);
        $this->assertSame(2, substr_count($second->getContent(), 'data-video-archive-card'));

        $this->get(route('videos.index', ['search' => 'آزمایشی 14']))
            ->assertOk()->assertSee('عنوان ویدئوی آزمایشی 14')
            ->assertDontSee('عنوان ویدئوی آزمایشی 13');
    }

    public function test_public_video_detail_displays_title_without_backend_video_type(): void
    {
        $video = $this->publishVideo(3, 'aparat');
        $this->get(route('videos.show', $video->slug))
            ->assertOk()
            ->assertSee($video->title)
            ->assertDontSee('آپلود مستقیم')
            ->assertDontSee('لینک آپارات');
    }

    public function test_archive_has_helpful_empty_and_no_result_states(): void
    {
        $this->get(route('videos.index'))
            ->assertOk()->assertSee('هنوز ویدئویی منتشر نشده است');

        $this->publishVideo(5);
        $this->get(route('videos.index', ['search' => 'عبارت-بدون-نتیجه']))
            ->assertOk()
            ->assertSee('ویدئویی با این عبارت پیدا نشد')
            ->assertSee('مشاهده همه ویدئوها');
    }
}
