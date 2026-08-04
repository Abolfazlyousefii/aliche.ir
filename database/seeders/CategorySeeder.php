<?php

namespace Database\Seeders;

use App\Models\AnnouncementCategory;
use App\Models\Category;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['اخبار اصناف', 'guild-news'], ['آموزش و قوانین', 'rules-training'], ['خدمات و سامانه‌ها', 'services-systems'], ['گردشگری گرگان', 'gorgan-tourism'], ['کمیسیون‌ها', 'commissions']] as $i => $c) {
            PostCategory::updateOrCreate(['slug' => $c[1]], ['title' => $c[0], 'sort_order' => $i + 1, 'is_active' => true]);
        }

        foreach ([['اطلاعیه عمومی', 'public-notices'], ['فراخوان‌ها', 'calls'], ['بخشنامه‌ها', 'directives'], ['آموزشی', 'training']] as $i => $c) {
            AnnouncementCategory::updateOrCreate(['slug' => $c[1]], ['title' => $c[0], 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $dynamicCategories = [
            'news' => [['اخبار عمومی', 'news-general'], ['اطلاعیه‌ها', 'news-announcements']],
            'tourism' => [['طبیعت‌گردی', 'tourism-nature'], ['تاریخی', 'tourism-historic']],
            'gallery' => [['گالری عمومی', 'gallery-general']],
            'video' => [['ویدیوهای عمومی', 'video-general']],
            'service' => [['خدمات عمومی', 'service-general']],
            'system' => [['سامانه‌های عمومی', 'system-general']],
            'union' => [['اتحادیه‌های عمومی', 'union-general']],
        ];

        foreach ($dynamicCategories as $type => $items) {
            foreach ($items as $index => [$title, $slug]) {
                Category::updateOrCreate(
                    ['slug' => $slug],
                    ['title' => $title, 'type' => $type, 'sort_order' => $index + 1, 'is_active' => true]
                );
            }
        }
    }
}
