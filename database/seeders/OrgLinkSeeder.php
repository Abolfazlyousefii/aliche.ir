<?php

namespace Database\Seeders;

use App\Models\OrgLink;
use Illuminate\Database\Seeder;

class OrgLinkSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['title' => 'اتاق اصناف شهرستان گرگان', 'description' => 'اطلاعات تماس، آدرس و راهنمای مراجعه حضوری', 'url' => route('contact.create'), 'icon' => '📍', 'target' => '_self', 'sort_order' => 1],
            ['title' => 'سامانه نوین اصناف', 'description' => 'پیگیری پرونده، درخواست‌ها و خدمات الکترونیک اصناف', 'url' => 'https://iranianasnaf.ir', 'icon' => '💻', 'target' => '_blank', 'sort_order' => 2],
            ['title' => 'سامانه ثبت شکایات', 'description' => 'ثبت و پیگیری شکایت صنفی', 'url' => route('complaints.create'), 'icon' => '⚖️', 'target' => '_self', 'sort_order' => 3],
            ['title' => 'اداره صمت گلستان', 'description' => 'ارتباط با اداره صنعت، معدن و تجارت استان گلستان', 'url' => '#', 'icon' => '🏛', 'target' => '_self', 'sort_order' => 4],
            ['title' => 'اتحادیه‌های صنفی', 'description' => 'مشاهده فهرست اتحادیه‌ها و اطلاعات تماس', 'url' => route('guilds.index'), 'icon' => '🤝', 'target' => '_self', 'sort_order' => 5],
            ['title' => 'فرم‌ها و بخشنامه‌ها', 'description' => 'مشاهده اطلاعیه‌ها، فرم‌ها و بخشنامه‌های جدید', 'url' => route('announcements.index'), 'icon' => '📋', 'target' => '_self', 'sort_order' => 6],
        ];

        foreach ($items as $item) {
            OrgLink::updateOrCreate(['title' => $item['title']], $item + ['is_active' => true]);
        }
    }
}
