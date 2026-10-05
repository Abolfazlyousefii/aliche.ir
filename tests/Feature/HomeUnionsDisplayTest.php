<?php

namespace Tests\Feature;

use App\Models\GuildUnion;
use App\Models\UnionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class HomeUnionsDisplayTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_home_union_directory_is_alphabetical_keeps_all_items_for_search_and_limits_initial_view_to_ten(): void
    {
        foreach (range(12, 1) as $number) {
            GuildUnion::query()->create([
                'name' => sprintf('اتحادیه تست %02d', $number),
                'title' => sprintf('اتحادیه تست %02d', $number),
                'slug' => 'home-union-'.$number,
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('اتحادیه‌های صنفی گلستان')
            ->assertSee('data-home-union-search', false)
            ->assertSee('aria-label="جستجوی اتحادیه"', false)
            ->assertSee('M13 4.5 7.5 10 13 15.5', false)
            ->assertDontSee('<span class="sr-only">جستجوی اتحادیه</span>', false)
            ->assertSee('مشاهده همه اتحادیه‌ها');

        $response->assertSeeInOrder([
            'اتحادیه تست 01',
            'اتحادیه تست 02',
            'اتحادیه تست 03',
            'اتحادیه تست 10',
            'اتحادیه تست 11',
            'اتحادیه تست 12',
        ]);

        $response
            ->assertSee('data-home-union-index="10"', false)
            ->assertSee('data-home-union-index="11"', false);

        $this->assertMatchesRegularExpression(
            '/data-home-union-index="10"[^>]*hidden/s',
            $response->getContent()
        );
    }

    public function test_home_union_feature_keeps_the_latest_published_union_news(): void
    {
        $union = GuildUnion::query()->create([
            'name' => 'اتحادیه خبر تست',
            'title' => 'اتحادیه خبر تست',
            'slug' => 'home-news-union',
            'is_active' => true,
        ]);

        $this->publishedPost([
            'title' => 'خبر قدیمی اتحادیه',
            'slug' => 'old-home-union-news',
            'union_id' => $union->id,
            'published_at' => now()->subDay(),
        ]);

        $latest = $this->publishedPost([
            'title' => 'جدیدترین خبر اتحادیه برای صفحه اصلی',
            'slug' => 'latest-home-union-news',
            'union_id' => $union->id,
            'published_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('آخرین خبر اتحادیه‌ها')
            ->assertSee($latest->title)
            ->assertSee(route('posts.show', $latest->slug), false);
    }

    public function test_active_unions_are_displayed_on_home_page(): void
    {
        $unionType = UnionType::query()->firstOrCreate(
            ['slug' => GuildUnion::TYPE_SERVICE],
            [
                'title' => 'اتحادیه‌های خدماتی',
                'icon' => '🧰',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        GuildUnion::query()->create([
            'name' => 'اتحادیه تست خدمات',
            'title' => 'اتحادیه تست خدمات',
            'slug' => 'test-service-union',
            'union_type' => GuildUnion::TYPE_SERVICE,
            'union_type_id' => $unionType->id,
            'short_description' => 'توضیح کوتاه اتحادیه تست',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('اتحادیه تست خدمات')
            ->assertDontSee('اتحادیه فعالی برای نمایش در صفحه اصلی ثبت نشده است.');
    }
}
