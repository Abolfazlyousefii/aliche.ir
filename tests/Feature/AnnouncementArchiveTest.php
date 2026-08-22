<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\Category;
use App\Models\GuildUnion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_only_shows_current_public_published_announcements(): void
    {
        $visible = $this->announcement('اطلاعیه فعال', 'visible-announcement');
        $this->announcement('اطلاعیه پیش‌نویس', 'draft-announcement', ['status' => 'draft']);
        $this->announcement('اطلاعیه غیرفعال', 'inactive-announcement', ['is_active' => false]);
        $this->announcement('اطلاعیه خصوصی', 'private-announcement', ['visibility' => 'private']);
        $this->announcement('اطلاعیه آینده', 'future-announcement', ['starts_at' => now()->addDay()]);
        $this->announcement('اطلاعیه منقضی', 'expired-announcement', ['expires_at' => now()->subMinute()]);

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertViewIs('frontend.announcements.index')
            ->assertSee($visible->title)
            ->assertDontSee('اطلاعیه پیش‌نویس')
            ->assertDontSee('اطلاعیه غیرفعال')
            ->assertDontSee('اطلاعیه خصوصی')
            ->assertDontSee('اطلاعیه آینده')
            ->assertDontSee('اطلاعیه منقضی')
            ->assertSee('announcements-archive-page', false)
            ->assertViewHas('announcements', fn ($items) => $items->total() === 1);
    }

    public function test_search_only_returns_matching_announcements(): void
    {
        $this->announcement('فراخوان ویژه اصناف گرگان', 'matching-announcement');
        $this->announcement('بخشنامه نامرتبط', 'unrelated-announcement');

        $this->get(route('announcements.index', ['search' => 'فراخوان ویژه']))
            ->assertOk()
            ->assertSee('فراخوان ویژه اصناف گرگان')
            ->assertDontSee('بخشنامه نامرتبط')
            ->assertViewHas('announcements', fn ($items) => $items->total() === 1);
    }

    public function test_category_filter_uses_only_announcement_categories_with_published_items(): void
    {
        $selected = $this->announcementCategory('فراخوان‌ها', 'calls');
        $other = $this->announcementCategory('بخشنامه‌ها', 'circulars');
        $unused = $this->announcementCategory('دسته بدون اطلاعیه', 'unused-announcements');
        Category::query()->create([
            'title' => 'اخبار عمومی',
            'slug' => 'general-news',
            'type' => 'news',
            'is_active' => true,
        ]);

        $this->announcement('فراخوان منتخب', 'selected-category', ['category_id' => $selected->id]);
        $this->announcement('بخشنامه دیگر', 'other-category', ['category_id' => $other->id]);

        $this->get(route('announcements.index', ['category_id' => $selected->id]))
            ->assertOk()
            ->assertSee('فراخوان منتخب')
            ->assertDontSee('بخشنامه دیگر')
            ->assertDontSee('اخبار عمومی')
            ->assertDontSee($unused->title)
            ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->sort()->values()->all() === [$selected->id, $other->id]);
    }

    public function test_union_filter_keeps_general_items_unfiltered_and_handles_invalid_values(): void
    {
        $selected = $this->union('اتحادیه منتخب', 'selected-union');
        $other = $this->union('اتحادیه دیگر', 'other-union');
        $this->announcement('اطلاعیه عمومی', 'general-announcement');
        $this->announcement('اطلاعیه اتحادیه منتخب', 'selected-union-announcement', ['union_id' => $selected->id]);
        $this->announcement('اطلاعیه اتحادیه دیگر', 'other-union-announcement', ['union_id' => $other->id]);

        $this->get(route('announcements.index'))
            ->assertSee('اطلاعیه عمومی')
            ->assertSee('اطلاعیه اتحادیه منتخب')
            ->assertSee('اطلاعیه اتحادیه دیگر');

        $this->get(route('announcements.index', ['union_id' => $selected->id]))
            ->assertOk()
            ->assertSee('اطلاعیه اتحادیه منتخب')
            ->assertDontSee('اطلاعیه عمومی')
            ->assertDontSee('اطلاعیه اتحادیه دیگر');

        $this->get(route('announcements.index', ['union_id' => 'invalid']))
            ->assertOk()
            ->assertViewHas('announcements', fn ($items) => $items->total() === 3);
    }

    public function test_card_without_image_is_compact_and_does_not_render_placeholder_media(): void
    {
        $this->announcement('اطلاعیه بدون تصویر', 'without-image', [
            'excerpt' => null,
            'body' => '<script>alert("unsafe")</script><p>متن امن اطلاعیه</p>',
            'featured_image' => null,
            'is_important' => true,
        ]);

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('announcement-card is-no-image is-important', false)
            ->assertSee('متن امن اطلاعیه')
            ->assertSee('announcement-card-document', false)
            ->assertDontSee('announcement-card-media', false)
            ->assertDontSee('asnaf-gorgan-default.jpg', false)
            ->assertDontSee('<script>alert("unsafe")</script>', false);
    }

    public function test_ajax_pagination_returns_shared_html_and_preserves_all_filters(): void
    {
        $category = $this->announcementCategory('اطلاع‌رسانی', 'notices');
        $union = $this->union('اتحادیه ایجکس', 'ajax-union');

        foreach (range(1, 13) as $index) {
            $this->announcement("اطلاعیه ایجکس {$index}", "ajax-announcement-{$index}", [
                'category_id' => $category->id,
                'union_id' => $union->id,
                'published_at' => now()->subMinutes($index),
            ]);
        }

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('announcements.index', [
            'search' => 'اطلاعیه ایجکس',
            'category_id' => $category->id,
            'union_id' => $union->id,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 13)
            ->assertJsonPath('from', 13)
            ->assertJsonPath('to', 13)
            ->assertJsonStructure(['html', 'current_page', 'last_page', 'total', 'from', 'to', 'url']);

        $html = $response->json('html');
        $this->assertStringContainsString('data-announcements-results', $html);
        $this->assertStringContainsString('announcements-pagination', $html);
        $this->assertStringContainsString('category_id='.$category->id, $html);
        $this->assertStringContainsString('union_id='.$union->id, $html);
        $this->assertStringContainsString('search=%D8%A7%D8%B7%D9%84%D8%A7%D8%B9%DB%8C%D9%87%20%D8%A7%DB%8C%D8%AC%DA%A9%D8%B3', $html);
    }

    private function announcement(string $title, string $slug, array $attributes = []): Announcement
    {
        return Announcement::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'خلاصه اطلاعیه برای آزمون آرشیو.',
            'body' => '<p>متن اطلاعیه</p>',
            'starts_at' => now()->subDay(),
            'expires_at' => null,
            'status' => 'published',
            'visibility' => 'public',
            'published_at' => now()->subHour(),
            'is_active' => true,
        ], $attributes));
    }

    private function announcementCategory(string $title, string $slug): AnnouncementCategory
    {
        return AnnouncementCategory::query()->create([
            'title' => $title,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function union(string $name, string $slug): GuildUnion
    {
        return GuildUnion::query()->create([
            'name' => $name,
            'title' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
}
