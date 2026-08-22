<?php

namespace Tests\Concerns;

use App\Models\Post;

trait BuildsAdminPayloads
{
    /** @return array<string, mixed> */
    protected function postPayload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'مطلب آزمایشی',
            'slug' => 'test-post',
            'excerpt' => 'خلاصه مطلب آزمایشی',
            'body' => '<p>متن مطلب آزمایشی</p>',
            'type' => 'news',
            'homepage_position' => 'normal',
            'is_important' => '0',
            'featured_order' => 0,
            'status' => 'draft',
            'published_at' => '',
            'sort_order' => 0,
            'meta_keywords' => '',
        ], $overrides);
    }

    /** @return array<string, mixed> */
    protected function unionPayload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'اتحادیه آزمایشی',
            'slug' => 'test-union',
            'news_mode' => 'auto',
            'price_list_mode' => 'table',
            'complaint_enabled' => '0',
            'congratulations_enabled' => '0',
            'news_enabled' => '1',
            'announcements_enabled' => '1',
            'gallery_enabled' => '0',
            'videos_enabled' => '0',
            'members_enabled' => '0',
            'services_enabled' => '0',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    protected function publishedPost(array $overrides = []): Post
    {
        return Post::query()->create(array_replace([
            'title' => 'مطلب منتشرشده',
            'slug' => 'published-post-'.uniqid(),
            'type' => 'news',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'is_active' => true,
        ], $overrides));
    }
}
