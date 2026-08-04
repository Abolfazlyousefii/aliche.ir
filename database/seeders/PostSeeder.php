<?php

namespace Database\Seeders;

use App\Models\GuildUnion;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostGallery;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $newsCategory = PostCategory::firstOrCreate(['slug' => 'guild-news'], ['title' => 'اخبار اصناف', 'is_active' => true]);
        $educationCategory = PostCategory::firstOrCreate(['slug' => 'guild-education'], ['title' => 'آموزش اصناف', 'is_active' => true]);
        $unions = GuildUnion::all()->values();

        $posts = [
            ['نشست هیئت رئیسه اتاق اصناف گرگان برگزار شد', $newsCategory->id, 'news', true],
            ['اطلاعیه مهم درباره تمدید پروانه کسب', $newsCategory->id, 'announcement', true],
            ['آغاز دوره آموزشی احکام تجارت برای واحدهای صنفی', $educationCategory->id, 'article', true],
            ['ویدیو راهنمای ثبت درخواست در سامانه نوین اصناف', $educationCategory->id, 'video', true],
            ['بازدید از واحدهای صنفی سطح شهر گرگان', $newsCategory->id, 'news', true],
            ['جلسه مشترک اتحادیه‌ها با مدیرکل برگزار شد', $newsCategory->id, 'news', true],
            ['طرح نظارت ویژه بازار گرگان آغاز شد', $newsCategory->id, 'news', false],
            ['تقدیر از پیشکسوتان اصناف شهرستان گرگان', $newsCategory->id, 'news', true],
            ['راه‌اندازی میز خدمت الکترونیک اتاق اصناف', $educationCategory->id, 'article', false],
            ['گزارش ویدیویی از نمایشگاه توانمندی‌های اصناف', $newsCategory->id, 'video', true],
            ['بررسی مشکلات واحدهای صنفی در کمیسیون نظارت', $newsCategory->id, 'news', false],
            ['اطلاع‌رسانی درباره سامانه نوین اصناف', $educationCategory->id, 'article', false],
        ];

        foreach ($posts as $index => [$title, $categoryId, $type, $hasGallery]) {
            $post = Post::updateOrCreate(
                ['slug' => 'gorgan-news-'.($index + 1)],
                [
                    'title' => $title,
                    'excerpt' => 'خلاصه خبر '.$title.' برای نمایش در کارت‌های صفحه اصلی، آرشیو نوشته‌ها و بخش‌های خبری سایت.',
                    'body' => '<p>'.$title.' به عنوان خبر قابل ویرایش اتاق اصناف مرکز استان گلستان در پایگاه داده ثبت شده است.</p><p>این متن شامل جزئیات خبر، توضیحات تکمیلی و اطلاعات قابل به‌روزرسانی توسط مدیر سامانه است.</p>',
                    'featured_image' => 'assets/img/asnaf-gorgan-default.jpg',
                    'category_id' => $categoryId,
                    'union_id' => $unions->isNotEmpty() ? $unions[$index % $unions->count()]->id : null,
                    'type' => $type,
                    'is_important' => $index < 6,
                    'is_featured' => $index < 4,
                    'views_count' => 100 + ($index * 23),
                    'status' => 'published',
                    'published_at' => now()->subDays($index),
                    'created_by' => $admin?->id,
                    'approved_by' => $admin?->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            if ($hasGallery) {
                for ($galleryIndex = 1; $galleryIndex <= 3; $galleryIndex++) {
                    PostGallery::updateOrCreate(
                        ['post_id' => $post->id, 'sort_order' => $galleryIndex],
                        ['image' => 'assets/img/asnaf-gorgan-default.jpg', 'caption' => 'تصویر '.$galleryIndex.' '.$title]
                    );
                }
            }
        }

        foreach ([1, 2, 3] as $index) {
            Post::updateOrCreate(
                ['slug' => 'pending-news-'.$index],
                [
                    'title' => 'خبر آماده بررسی '.$index,
                    'excerpt' => 'این خبر برای بررسی و تایید مدیر محتوا ثبت شده است.',
                    'body' => '<p>این خبر پس از تایید مدیر محتوا قابلیت انتشار در سایت را خواهد داشت.</p>',
                    'category_id' => $newsCategory->id,
                    'status' => 'pending',
                    'published_at' => null,
                    'created_by' => $admin?->id,
                    'sort_order' => 100 + $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
