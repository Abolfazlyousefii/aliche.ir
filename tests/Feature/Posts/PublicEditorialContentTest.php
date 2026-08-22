<?php

namespace Tests\Feature\Posts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class PublicEditorialContentTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_archive_displays_published_editorial_types_and_hides_unpublished_content(): void
    {
        $visible = collect([
            ['خبر آرشیو تست', 'archive-news', 'news'],
            ['گفتگو آرشیو تست', 'archive-interview', 'interview'],
            ['گزارش آرشیو تست', 'archive-report', 'report'],
            ['یادداشت آرشیو تست', 'archive-note', 'note'],
        ])->map(fn (array $item) => $this->publishedPost([
            'title' => $item[0],
            'slug' => $item[1],
            'type' => $item[2],
        ]));
        $hidden = $this->publishedPost([
            'title' => 'گزارش منتشرنشده تست',
            'slug' => 'unpublished-report',
            'type' => 'report',
            'status' => 'draft',
        ]);

        $response = $this->get(route('posts.index'))->assertOk();
        $visible->each(fn ($post) => $response->assertSee($post->title));
        $response->assertDontSee($hidden->title);
    }

    public function test_search_finds_published_interview_report_and_note_but_not_drafts(): void
    {
        foreach ([
            ['گفتگوی کلیدآزمون', 'search-interview', 'interview'],
            ['گزارش کلیدآزمون', 'search-report', 'report'],
            ['یادداشت کلیدآزمون', 'search-note', 'note'],
        ] as [$title, $slug, $type]) {
            $this->publishedPost(compact('title', 'slug', 'type'));
        }
        $draft = $this->publishedPost([
            'title' => 'پیش‌نویس کلیدآزمون',
            'slug' => 'search-draft',
            'type' => 'interview',
            'status' => 'draft',
        ]);

        $this->get(route('search', ['q' => 'کلیدآزمون']))
            ->assertOk()
            ->assertSee('گفتگوی کلیدآزمون')
            ->assertSee('گزارش کلیدآزمون')
            ->assertSee('یادداشت کلیدآزمون')
            ->assertDontSee($draft->title);
    }
}
