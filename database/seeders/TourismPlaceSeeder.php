<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\TourismPlace;
use App\Models\User;
use Illuminate\Database\Seeder;

class TourismPlaceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $category = PostCategory::firstOrCreate(
            ['slug' => 'gorgan-tourism'],
            ['title' => 'گردشگری گرگان', 'is_active' => true]
        );

        $groups = [
            'nature' => [
                ['جنگل ناهارخوران', 'طبیعت', 'گرگان، بلوار ناهارخوران', 'جنگل ناهارخوران از شناخته‌شده‌ترین جاذبه‌های طبیعی گرگان برای پیاده‌روی، تفریح خانوادگی و طبیعت‌گردی است.'],
                ['پارک جنگلی النگدره', 'پارک جنگلی', 'گرگان، مسیر ناهارخوران', 'النگدره با مسیرهای سرسبز، هوای خنک و فضای مناسب گردش روزانه از نقاط محبوب شهر گرگان است.'],
                ['روستای زیارت', 'ییلاق', 'جنوب گرگان، روستای زیارت', 'روستای زیارت با بافت روستایی، چشم‌انداز کوهستانی و آب‌وهوای مطلوب مقصدی جذاب برای گردشگران است.'],
            ],
            'historic' => [
                ['کاخ‌موزه گرگان', 'تاریخی', 'گرگان، مرکز شهر', 'کاخ‌موزه گرگان بخشی از حافظه تاریخی شهر را روایت می‌کند و برای آشنایی با میراث فرهنگی منطقه مناسب است.'],
                ['بافت تاریخی گرگان', 'میراث شهری', 'محله‌های تاریخی گرگان', 'بافت تاریخی گرگان با خانه‌های قدیمی، گذرها و نشانه‌های معماری بومی یکی از ظرفیت‌های مهم گردشگری شهر است.'],
                ['مدرسه عمادیه', 'بنای تاریخی', 'گرگان، بافت قدیم', 'مدرسه عمادیه از بناهای ارزشمند تاریخی گرگان است و در معرفی پیشینه فرهنگی و آموزشی شهر نقش دارد.'],
            ],
            'shop' => [
                ['بازار نعلبندان', 'بازار سنتی', 'گرگان، بازار نعلبندان', 'بازار نعلبندان مرکز خرید سنتی و یکی از نقاط مهم تعامل اصناف، شهروندان و گردشگران در گرگان است.'],
                ['بازارچه صنایع دستی', 'سوغات', 'گرگان، محدوده مرکزی شهر', 'بازارچه صنایع دستی محل عرضه محصولات بومی، سوغات و آثار هنرمندان محلی استان گلستان است.'],
                ['مراکز خرید گرگان', 'خرید', 'گرگان، خیابان‌های اصلی شهر', 'مراکز خرید گرگان برای معرفی کسب‌وکارهای شهری، محصولات صنفی و خدمات مورد نیاز مسافران کاربرد دارند.'],
            ],
        ];

        $order = 1;
        foreach ($groups as $type => $places) {
            foreach ($places as $index => [$title, $badge, $location, $description]) {
                TourismPlace::updateOrCreate(
                    ['slug' => $type.'-tourism-'.($index + 1)],
                    [
                        'title' => $title,
                        'description' => '<p>'.$description.'</p>',
                        'short_description' => $description,
                        'featured_image' => 'assets/img/asnaf-gorgan-default.jpg',
                        'image' => 'assets/img/asnaf-gorgan-default.jpg',
                        'gallery' => array_fill(0, 4, ['path' => 'assets/img/asnaf-gorgan-default.jpg', 'caption' => $title]),
                        'category_id' => $category->id,
                        'badge' => $badge,
                        'location' => $location,
                        'type' => $type,
                        'address' => 'استان گلستان، شهرستان گرگان، '.$location,
                        'map_url' => 'https://maps.google.com',
                        'latitude' => 36.84 + ($order / 100),
                        'longitude' => 54.43 + ($order / 100),
                        'phone' => '01732220000',
                        'working_hours' => 'همه روزه از ۸ تا ۲۰',
                        'visit_price' => 'رایگان / طبق تعرفه محل',
                        'status' => 'published',
                        'published_at' => now()->subDays($order),
                        'created_by' => $admin?->id,
                        'approved_by' => $admin?->id,
                        'sort_order' => $order,
                        'is_active' => true,
                    ]
                );

                $order++;
            }
        }
    }
}
