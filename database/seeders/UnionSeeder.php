<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\GuildUnion;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\UnionCommission;
use App\Models\UnionCommissionTask;
use App\Models\UnionEducation;
use App\Models\UnionMember;
use App\Models\UnionMinute;
use App\Models\UnionPrice;
use App\Models\UnionRule;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class UnionSeeder extends Seeder
{
    public function run(): void
    {
        $typeCategories = collect(GuildUnion::typeLabels())->mapWithKeys(fn ($title, $type) => [
            $type => Category::updateOrCreate(
                ['slug' => 'union-type-'.$type],
                ['title' => $title, 'type' => 'union_type', 'sort_order' => array_search($type, array_keys(GuildUnion::typeLabels()), true) + 1, 'is_active' => true]
            ),
        ]);

        $defaults = GuildUnion::sectionDefaults();
        $generalSettings = array_merge($defaults, [
            'show_prices' => false,
            'show_rules' => true,
            'show_commissions' => true,
            'show_complaint' => true,
            'show_social_links' => true,
        ]);
        $goldSettings = array_merge($generalSettings, [
            'show_prices' => true,
            'show_commission_tasks' => true,
        ]);

        $items = [
            [GuildUnion::TYPE_PRODUCTION, 'اتحادیه صنف خبازان', 'bakers-union', 'نانوایی‌ها و واحدهای مرتبط با تولید نان', 'سید رضا موسوی', '01732221001'],
            [GuildUnion::TYPE_PRODUCTION, 'اتحادیه صنف چاپ و تکثیر', 'printing-copy-union', 'چاپخانه‌ها، تکثیر و خدمات چاپی', 'حسین احمدی', '01732221002'],
            [GuildUnion::TYPE_PRODUCTION, 'اتحادیه صنف درودگران', 'woodworkers-union', 'تولید و خدمات چوب، کابینت و مصنوعات چوبی', 'مریم حسینی', '01732221003'],
            [GuildUnion::TYPE_PRODUCTION, 'اتحادیه صنف قنادان', 'confectioners-union', 'شیرینی‌پزی، قنادی و تولید محصولات مرتبط', 'علی کاظمی', '01732221004'],
            [GuildUnion::TYPE_PRODUCTION, 'اتحادیه صنف لبنیات', 'dairy-producers-union', 'تولید و عرضه لبنیات سنتی و صنعتی', 'رضا محمدی', '01732221005'],
            [GuildUnion::TYPE_DISTRIBUTION, 'اتحادیه خواربار و مواد غذایی', 'grocery-food-union', 'فروشگاه‌ها و واحدهای عرضه مواد غذایی', 'سعید مرادی', '01732221009'],
            [GuildUnion::TYPE_DISTRIBUTION, 'اتحادیه صنف پوشاک', 'clothing-union', 'فروشندگان پوشاک، منسوجات و محصولات مرتبط', 'فاطمه کریمی', '01732221008'],
            [GuildUnion::TYPE_DISTRIBUTION, 'اتحادیه صنف طلا و جواهر', 'gold-jewelry-union', 'واحدهای فروش، ساخت و تعمیرات طلا و جواهر', 'محمد صادقی', '01732221007'],
            [GuildUnion::TYPE_DISTRIBUTION, 'اتحادیه صنف میوه و تره‌بار', 'fruit-vegetable-union', 'فروشندگان میوه، سبزی و صیفی‌جات', 'حمید ناصری', '01732221011'],
            [GuildUnion::TYPE_DISTRIBUTION, 'اتحادیه لوازم خانگی', 'home-appliances-union', 'عرضه‌کنندگان لوازم خانگی و کالای بادوام', 'نرگس رضایی', '01732221010'],
            [GuildUnion::TYPE_SERVICE, 'اتحادیه تعمیرکاران خودرو', 'car-repair-union', 'خدمات تعمیر، صافکاری و امور فنی خودرو', 'حسن محمودی', '01732221013'],
            [GuildUnion::TYPE_SERVICE, 'اتحادیه خدمات رایانه', 'computer-services-union', 'خدمات فنی، پشتیبانی و تعمیر تجهیزات رایانه‌ای', 'امیر صادقی', '01732221017'],
            [GuildUnion::TYPE_SERVICE, 'اتحادیه صنف آرایشگران', 'hairdressers-union', 'خدمات آرایشی، پیرایشی و بهداشتی مجاز', 'مجید صفری', '01732221015'],
            [GuildUnion::TYPE_SERVICE, 'اتحادیه مشاوران املاک', 'real-estate-union', 'خدمات خرید، فروش، رهن و اجاره املاک', 'الهام رستمی', '01732221014'],
            [GuildUnion::TYPE_SERVICE, 'اتحادیه رستوران و اغذیه', 'restaurants-catering-union', 'رستوران‌ها، اغذیه‌فروشی‌ها و خدمات پذیرایی', 'لیلا جعفری', '01732221016'],
            [GuildUnion::TYPE_SPECIALIZED, 'اتحادیه تجهیزات پزشکی', 'medical-equipment-union', 'عرضه و خدمات تجهیزات پزشکی و سلامت', 'فرزاد بهرامی', '01732221019'],
            [GuildUnion::TYPE_SPECIALIZED, 'اتحادیه رایانه و فناوری اطلاعات', 'it-specialized-union', 'رسته‌های تخصصی فناوری اطلاعات و ارتباطات', 'ندا شریفی', '01732221020'],
            [GuildUnion::TYPE_SPECIALIZED, 'اتحادیه ساعت و عینک', 'watch-optical-union', 'واحدهای تخصصی ساعت، عینک و خدمات وابسته', 'کامران جلالی', '01732221021'],
            [GuildUnion::TYPE_SPECIALIZED, 'اتحادیه صنایع دستی', 'handicrafts-union', 'تولید و عرضه صنایع دستی و هنرهای سنتی', 'مهسا علیزاده', '01732221022'],
            [GuildUnion::TYPE_SPECIALIZED, 'اتحادیه فروشندگان لوازم صوتی و تصویری', 'audio-video-equipment-union', 'رسته‌های تخصصی صوتی، تصویری و دیجیتال', 'بهنام سعیدی', '01732221023'],
        ];

        $sortedItems = collect($items)->sortBy(fn ($item) => $item[1], SORT_NATURAL)->values();

        foreach ($sortedItems as $index => [$type, $title, $slug, $shortDescription, $manager, $phone]) {
            $isGold = $slug === 'gold-jewelry-union';
            GuildUnion::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'name' => $title,
                    'union_type' => $type,
                    'category_id' => $typeCategories[$type]->id,
                    'logo' => 'assets/img/asnaf-seal.svg',
                    'cover_image' => 'assets/img/asnaf-gorgan-default.jpg',
                    'description' => $isGold
                        ? 'اتحادیه صنف طلا و جواهر گرگان مسئول ساماندهی واحدهای فروش، ساخت، تعمیرات، نرخ‌نامه‌ها، آموزش تخصصی و نظارت بر اجرای قوانین این صنف است.'
                        : 'این اتحادیه با هدف ساماندهی واحدهای صنفی، رسیدگی به امور اعضا، آموزش، نظارت و پاسخگویی به شهروندان در شهرستان گرگان فعالیت می‌کند.',
                    'short_description' => $shortDescription,
                    'address' => 'گرگان، خیابان ولیعصر، ساختمان اتاق اصناف، طبقه '.(($index % 3) + 1),
                    'phone' => $phone,
                    'mobile' => '0911'.str_pad((string) (2000000 + $index), 7, '0', STR_PAD_LEFT),
                    'email' => str_replace('-', '.', $slug).'@asnaf-gorgan.ir',
                    'website' => 'https://asnaf-gorgan.ir',
                    'manager_name' => $manager,
                    'manager_image' => 'assets/img/asnaf-gorgan-default.jpg',
                    'working_hours' => 'شنبه تا چهارشنبه ۸ تا ۱۴',
                    'social_links' => ['telegram' => 'https://t.me/asnaf_gorgan', 'eitaa' => 'https://eitaa.com/asnaf_gorgan', 'website' => 'https://asnaf-gorgan.ir'],
                    'settings' => $isGold ? $goldSettings : $generalSettings,
                    'is_active' => true,
                    'complaint_enabled' => true,
                    'congratulations_enabled' => true,
                    'news_enabled' => true,
                    'announcements_enabled' => true,
                    'gallery_enabled' => true,
                    'videos_enabled' => true,
                    'members_enabled' => true,
                    'services_enabled' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $gold = GuildUnion::where('slug', 'gold-jewelry-union')->first();
        if ($gold) {
            $this->seedGoldUnionContent($gold);
        }
    }

    private function seedGoldUnionContent(GuildUnion $union): void
    {
        $admin = User::query()->first();
        $postCategory = PostCategory::query()->where('slug', 'guild-news')->first();
        $announcementCategory = AnnouncementCategory::query()->where('slug', 'directives')->first();

        foreach ([
            ['محمد صادقی', 'رئیس اتحادیه', 1],
            ['علی احمدی', 'نایب رئیس', 2],
            ['حسن کریمی', 'دبیر اتحادیه', 3],
            ['سعید موسوی', 'خزانه‌دار', 4],
            ['رضا صادقی', 'بازرس', 5],
        ] as [$name, $position, $order]) {
            UnionMember::updateOrCreate(
                ['union_id' => $union->id, 'full_name' => $name],
                ['position' => $position, 'status' => 'active', 'is_active' => true, 'sort_order' => $order, 'description' => 'عضو هیئت مدیره اتحادیه طلا و جواهر گرگان']
            );
        }

        foreach ([
            ['کمیسیون نظارت و بازرسی', 'نظارت بر واحدهای صنفی و رعایت مقررات', ['بازرسی دوره‌ای واحدها', 'ثبت گزارش تخلف', 'پیگیری شکایات مردمی']],
            ['کمیسیون حل اختلاف', 'رسیدگی به اختلافات صنفی بین اعضا و مشتریان', ['دریافت درخواست', 'برگزاری جلسه صلح و سازش', 'تنظیم گزارش نهایی']],
            ['کمیسیون نرخ‌گذاری', 'تعیین و بروزرسانی نرخ اجرت و خدمات', ['بررسی نرخ بازار', 'اعلام نرخ مصوب', 'پایش اجرای نرخ‌نامه']],
            ['کمیسیون آموزش', 'برگزاری دوره‌های آموزشی تخصصی', ['نیازسنجی آموزشی', 'برنامه‌ریزی دوره‌ها', 'ارزیابی شرکت‌کنندگان']],
        ] as $index => [$title, $description, $tasks]) {
            $commission = UnionCommission::updateOrCreate(
                ['union_id' => $union->id, 'title' => $title],
                ['description' => $description, 'sort_order' => $index + 1, 'is_active' => true]
            );

            foreach ($tasks as $taskIndex => $taskTitle) {
                UnionCommissionTask::updateOrCreate(
                    ['union_commission_id' => $commission->id, 'title' => $taskTitle],
                    ['sort_order' => $taskIndex + 1, 'is_active' => true]
                );
            }
        }

        foreach ([
            ['دستورالعمل نحوه خرید و فروش طلا', 'ابلاغیه ضوابط خرید و فروش، صدور فاکتور و احراز هویت مشتری', '📋'],
            ['ضوابط ساخت و تعمیرات طلا و جواهر', 'آیین‌نامه اجرایی مربوط به واحدهای ساخت، تعمیرات و خدمات تخصصی', '⚖️'],
            ['نرخ اجرت ساخت مصوب اتحادیه', 'راهنمای اعمال اجرت ساخت و خدمات مصنوعات طلا و جواهر', '💰'],
        ] as $index => [$title, $description, $icon]) {
            UnionRule::updateOrCreate(['union_id' => $union->id, 'title' => $title], ['description' => $description, 'icon' => $icon, 'sort_order' => $index + 1, 'is_active' => true]);
        }

        foreach ([
            ['طلای ۱۸ عیار', 35200000, 'gold'],
            ['سکه امامی', 415000000, 'coin'],
            ['نیم سکه', 230000000, 'coin'],
            ['اجرت پایه ساخت', 1500000, 'service'],
        ] as $index => [$title, $price, $type]) {
            UnionPrice::updateOrCreate(['union_id' => $union->id, 'title' => $title], ['price' => $price, 'currency' => 'ریال', 'type' => $type, 'updated_on' => now()->toDateString(), 'sort_order' => $index + 1, 'is_active' => true]);
        }

        foreach ([
            ['صورتجلسه بررسی نرخ‌نامه بهار', now()->subDays(20)->toDateString()],
            ['صورتجلسه کمیسیون آموزش تخصصی', now()->subDays(45)->toDateString()],
        ] as $index => [$title, $date]) {
            UnionMinute::updateOrCreate(['union_id' => $union->id, 'title' => $title], ['meeting_date' => $date, 'description' => 'مصوبات جلسه در دبیرخانه اتحادیه ثبت و قابل پیگیری است.', 'sort_order' => $index + 1, 'is_active' => true]);
        }

        foreach ([
            ['دوره تخصصی قوانین فروش طلا', 'آشنایی با الزامات فاکتور، مالیات و حقوق مصرف‌کننده', '📚'],
            ['کارگاه امنیت واحدهای طلافروشی', 'آموزش نکات ایمنی، بیمه و حفاظت فیزیکی واحد صنفی', '🛡️'],
        ] as $index => [$title, $description, $icon]) {
            UnionEducation::updateOrCreate(['union_id' => $union->id, 'title' => $title], ['description' => $description, 'icon' => $icon, 'link' => route('electronic-services.index'), 'sort_order' => $index + 1, 'is_active' => true]);
        }

        foreach ([
            ['اطلاعیه بروزرسانی نرخ اجرت طلا', 'نرخ‌های جدید اجرت ساخت و خدمات از ابتدای ماه جاری اعمال می‌شود.'],
            ['بخشنامه الزامات صدور فاکتور', 'تمام واحدهای عضو ملزم به صدور فاکتور رسمی و درج مشخصات کامل هستند.'],
        ] as $index => [$title, $excerpt]) {
            Announcement::updateOrCreate(['slug' => 'gold-'.($index + 1).'-announcement'], ['title' => $title, 'excerpt' => $excerpt, 'body' => $excerpt, 'category_id' => $announcementCategory?->id, 'union_id' => $union->id, 'starts_at' => now()->subDays($index + 1), 'is_important' => $index === 0, 'status' => 'published', 'published_at' => now()->subDays($index + 1), 'created_by' => $admin?->id, 'approved_by' => $admin?->id, 'sort_order' => $index + 1, 'is_active' => true]);
        }

        foreach ([
            ['خبر نشست اتحادیه طلا و جواهر با اعضا', 'در این نشست مهم‌ترین چالش‌های صنف بررسی شد.', 'news'],
            ['آموزش تخصصی تشخیص مصنوعات استاندارد', 'راهنمای عمومی برای اعضای صنف طلا و جواهر.', 'article'],
        ] as $index => [$title, $excerpt, $type]) {
            Post::updateOrCreate(['slug' => 'gold-'.($index + 1).'-'.$type], ['title' => $title, 'excerpt' => $excerpt, 'body' => $excerpt, 'featured_image' => 'assets/img/asnaf-gorgan-default.jpg', 'category_id' => $postCategory?->id, 'union_id' => $union->id, 'type' => $type, 'is_top' => $index === 0, 'status' => 'published', 'published_at' => now()->subDays($index + 2), 'created_by' => $admin?->id, 'approved_by' => $admin?->id, 'sort_order' => $index + 1, 'is_active' => true]);
        }

        $gallery = Gallery::updateOrCreate(['slug' => 'gold-jewelry-gallery'], ['title' => 'گالری اتحادیه طلا و جواهر', 'description' => 'تصاویر نشست‌ها و فعالیت‌های اتحادیه طلا و جواهر', 'cover_image' => 'assets/img/asnaf-gorgan-default.jpg', 'union_id' => $union->id, 'status' => 'published', 'published_at' => now()->subDays(3), 'created_by' => $admin?->id, 'approved_by' => $admin?->id, 'is_active' => true]);
        GalleryImage::updateOrCreate(['gallery_id' => $gallery->id, 'image' => 'assets/img/asnaf-gorgan-default.jpg'], ['caption' => 'نشست اتحادیه طلا و جواهر', 'sort_order' => 1]);

        Video::updateOrCreate(['slug' => 'gold-jewelry-video'], ['title' => 'ویدیوی معرفی اتحادیه طلا و جواهر', 'description' => 'معرفی خدمات و برنامه‌های اتحادیه', 'cover_image' => 'assets/img/asnaf-gorgan-default.jpg', 'video_type' => 'aparat', 'aparat_url' => 'https://www.aparat.com', 'union_id' => $union->id, 'status' => 'published', 'published_at' => now()->subDays(4), 'created_by' => $admin?->id, 'approved_by' => $admin?->id, 'is_active' => true]);
    }
}
