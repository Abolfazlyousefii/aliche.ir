<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\CongratulationMessage;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\GuildUnion;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\SiteSetting;
use App\Models\System as BusinessSystem;
use App\Models\TourismPlace;
use App\Models\UnionMember;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->adminUser();
        $categories = $this->categories();
        $announcementCategories = $this->announcementCategories();
        $unions = $this->unions();

        $this->settings();
        $this->homeSections();
        $this->pages($admin);
        $this->menus();
        $this->posts($admin, $categories, $unions);
        $this->announcements($admin, $announcementCategories, $unions);
        $this->unionMembers($unions);
        $this->tourism($admin, $categories);
        $this->galleries($admin, $categories, $unions);
        $this->videos($admin, $categories, $unions);
        $this->systems($admin, $categories);
        $this->electronicServices($admin, $categories);
        $this->commissions($admin);
        $this->congratulationMessages($admin, $unions);
    }

    private function adminUser(): User
    {
        return User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدیرکل سامانه',
                'mobile' => '09110000000',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    /** @return array<string, PostCategory> */
    private function categories(): array
    {
        $items = [
            ['title' => 'اخبار اصناف', 'slug' => 'guild-news', 'sort_order' => 1],
            ['title' => 'آموزش و قوانین', 'slug' => 'rules-training', 'sort_order' => 2],
            ['title' => 'خدمات و سامانه‌ها', 'slug' => 'services-systems', 'sort_order' => 3],
            ['title' => 'گردشگری گرگان', 'slug' => 'gorgan-tourism', 'sort_order' => 4],
        ];

        return collect($items)->mapWithKeys(fn (array $item) => [
            $item['slug'] => PostCategory::updateOrCreate(
                ['slug' => $item['slug']],
                ['title' => $item['title'], 'sort_order' => $item['sort_order'], 'is_active' => true]
            ),
        ])->all();
    }

    /** @return array<string, AnnouncementCategory> */
    private function announcementCategories(): array
    {
        $items = [
            ['title' => 'اطلاعیه عمومی', 'slug' => 'public-notices', 'sort_order' => 1],
            ['title' => 'فراخوان‌ها', 'slug' => 'calls', 'sort_order' => 2],
            ['title' => 'بخشنامه‌ها', 'slug' => 'directives', 'sort_order' => 3],
        ];

        return collect($items)->mapWithKeys(fn (array $item) => [
            $item['slug'] => AnnouncementCategory::updateOrCreate(
                ['slug' => $item['slug']],
                ['title' => $item['title'], 'sort_order' => $item['sort_order'], 'is_active' => true]
            ),
        ])->all();
    }

    /** @return array<string, GuildUnion> */
    private function unions(): array
    {
        $items = [
            [
                'title' => 'اتحادیه صنف پوشاک گرگان',
                'name' => 'اتحادیه پوشاک',
                'slug' => 'gorgan-clothing-union',
                'manager_name' => 'حسین احمدی',
                'phone' => '01732220001',
                'sort_order' => 1,
            ],
            [
                'title' => 'اتحادیه صنف مواد غذایی گرگان',
                'name' => 'اتحادیه مواد غذایی',
                'slug' => 'gorgan-food-union',
                'manager_name' => 'مریم حسینی',
                'phone' => '01732220002',
                'sort_order' => 2,
            ],
            [
                'title' => 'اتحادیه صنف فناوران رایانه گرگان',
                'name' => 'اتحادیه فناوران رایانه',
                'slug' => 'gorgan-it-union',
                'manager_name' => 'رضا محمدی',
                'phone' => '01732220003',
                'sort_order' => 3,
            ],
        ];

        return collect($items)->mapWithKeys(fn (array $item) => [
            $item['slug'] => GuildUnion::updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, [
                    'description' => 'این اتحادیه برای ساماندهی، پشتیبانی و نظارت بر واحدهای صنفی عضو در شهرستان گرگان فعالیت می‌کند.',
                    'short_description' => 'پشتیبانی و ساماندهی واحدهای صنفی عضو در شهرستان گرگان.',
                    'address' => 'گرگان، خیابان ولیعصر، ساختمان اتاق اصناف',
                    'email' => $item['slug'].'@example.com',
                    'is_active' => true,
                    'complaint_enabled' => true,
                    'congratulations_enabled' => true,
                    'news_enabled' => true,
                    'announcements_enabled' => true,
                    'gallery_enabled' => true,
                    'videos_enabled' => true,
                    'members_enabled' => true,
                    'services_enabled' => true,
                ])
            ),
        ])->all();
    }

    private function settings(): void
    {
        $settings = [
            'site.site_title' => ['group' => 'site', 'value' => 'اتاق اصناف شهرستان گرگان'],
            'site.site_description' => ['group' => 'site', 'value' => 'پایگاه اطلاع‌رسانی و خدمات الکترونیک اتاق اصناف شهرستان گرگان'],
            'site.default_meta_title' => ['group' => 'site', 'value' => 'اتاق اصناف شهرستان گرگان'],
            'site.default_meta_description' => ['group' => 'site', 'value' => 'اخبار، اطلاعیه‌ها، اتحادیه‌ها و خدمات صنفی شهرستان گرگان'],
            'header.top_text' => ['group' => 'header', 'value' => 'اتاق اصناف شهرستان گرگان؛ پشتیبان کسب‌وکارهای صنفی'],
            'header.contact_button_text' => ['group' => 'header', 'value' => 'تماس با اتاق'],
            'header.contact_button_link' => ['group' => 'header', 'value' => route('contact.create')],
            'header.service_button_text' => ['group' => 'header', 'value' => 'سامانه‌های صنفی'],
            'header.service_button_link' => ['group' => 'header', 'value' => route('systems.index')],
            'footer.copyright' => ['group' => 'footer', 'value' => 'تمام حقوق برای اتاق اصناف شهرستان گرگان محفوظ است.'],
        ];

        foreach ($settings as $key => $data) {
            SiteSetting::updateOrCreate(['key' => $key], $data);
        }
    }

    private function homeSections(): void
    {
        $labels = HomeSection::keyLabels();

        foreach (HomeSection::DEFAULT_KEYS as $index => $key) {
            HomeSection::updateOrCreate(
                ['key' => $key],
                [
                    'title' => $labels[$key] ?? $key,
                    'subtitle' => 'آخرین اطلاعات و محتوای به‌روزرسانی‌شده',
                    'settings' => ['limit' => in_array($key, ['hero_slider', 'important_news'], true) ? 4 : 6],
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]
            );
        }
    }

    private function pages(User $admin): void
    {
        $pages = [
            ['title' => 'درباره اتاق اصناف گرگان', 'slug' => 'about-gorgan-guild-chamber'],
            ['title' => 'راهنمای ارباب رجوع', 'slug' => 'citizen-guide'],
            ['title' => 'قوانین و مقررات صنفی', 'slug' => 'guild-rules'],
        ];

        foreach ($pages as $index => $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'excerpt' => 'معرفی و راهنمای کوتاه برای استفاده شهروندان و فعالان صنفی.',
                    'body' => '<p>این صفحه نمونه برای بررسی قالب و محتوای سایت ایجاد شده است.</p><p>اطلاعات نهایی توسط مدیر سامانه قابل ویرایش است.</p>',
                    'template' => 'default',
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 2),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function menus(): void
    {
        $menus = [
            'main' => 'منوی اصلی',
            'top' => 'منوی بالایی',
            'footer' => 'منوی فوتر',
            'quick' => 'دسترسی سریع',
        ];

        foreach ($menus as $location => $title) {
            $menu = Menu::updateOrCreate(
                ['location' => $location],
                ['title' => $title, 'is_active' => true]
            );

            $items = match ($location) {
                'main' => [
                    ['title' => 'صفحه اصلی', 'route_name' => 'home', 'url' => route('home')],
                    ['title' => 'اخبار', 'route_name' => 'posts.index', 'url' => route('posts.index')],
                    ['title' => 'اطلاعیه‌ها', 'route_name' => 'announcements.index', 'url' => route('announcements.index')],
                    ['title' => 'اتحادیه‌ها', 'route_name' => 'guilds.index', 'url' => route('guilds.index')],
                    ['title' => 'تماس با ما', 'route_name' => 'contact.create', 'url' => route('contact.create')],
                ],
                'quick' => [
                    ['title' => 'ثبت شکایت', 'route_name' => 'complaints.create', 'url' => route('complaints.create')],
                    ['title' => 'پیگیری شکایت', 'route_name' => 'complaints.track', 'url' => route('complaints.track')],
                    ['title' => 'سامانه‌ها', 'route_name' => 'systems.index', 'url' => route('systems.index')],
                    ['title' => 'خدمات الکترونیک', 'route_name' => 'electronic-services.index', 'url' => route('electronic-services.index')],
                ],
                default => [
                    ['title' => 'درباره ما', 'url' => route('pages.show', 'about-gorgan-guild-chamber')],
                    ['title' => 'قوانین صنفی', 'url' => route('pages.show', 'guild-rules')],
                    ['title' => 'تماس با ما', 'route_name' => 'contact.create', 'url' => route('contact.create')],
                ],
            };

            foreach ($items as $index => $item) {
                MenuItem::updateOrCreate(
                    ['menu_id' => $menu->id, 'title' => $item['title'], 'parent_id' => null],
                    [
                        'type' => 'custom',
                        'url' => $item['url'],
                        'route_name' => $item['route_name'] ?? null,
                        'target' => '_self',
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /** @param array<string, PostCategory> $categories @param array<string, GuildUnion> $unions */
    private function posts(User $admin, array $categories, array $unions): void
    {
        $posts = [
            ['title' => 'برگزاری نشست هم‌اندیشی روسای اتحادیه‌ها', 'slug' => 'guild-presidents-meeting', 'important' => true],
            ['title' => 'آغاز طرح نظارت ویژه بازار', 'slug' => 'market-monitoring-plan', 'important' => true],
            ['title' => 'راهنمای تمدید پروانه کسب برای واحدهای صنفی', 'slug' => 'business-license-renewal-guide', 'important' => false],
            ['title' => 'گزارش خدمات الکترونیک اصناف گرگان', 'slug' => 'electronic-services-report', 'important' => false],
        ];

        foreach ($posts as $index => $post) {
            Post::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'excerpt' => 'خلاصه‌ای از تازه‌ترین رویدادها و خدمات اتاق اصناف شهرستان گرگان.',
                    'body' => '<p>این خبر نمونه برای آزمایش نمایش اخبار، جستجو، فیلتر و صفحه جزئیات ایجاد شده است.</p>',
                    'category_id' => $categories['guild-news']->id,
                    'union_id' => array_values($unions)[$index % count($unions)]->id,
                    'type' => 'news',
                    'is_important' => $post['important'],
                    'is_featured' => $index === 0,
                    'views_count' => 120 + ($index * 35),
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, AnnouncementCategory> $categories @param array<string, GuildUnion> $unions */
    private function announcements(User $admin, array $categories, array $unions): void
    {
        $announcements = [
            ['title' => 'اطلاعیه ساعات پاسخگویی دبیرخانه', 'slug' => 'secretariat-hours'],
            ['title' => 'فراخوان تکمیل اطلاعات واحدهای صنفی', 'slug' => 'guild-info-call'],
            ['title' => 'بخشنامه رعایت ضوابط بهداشتی بازار', 'slug' => 'market-health-directive'],
        ];

        foreach ($announcements as $index => $announcement) {
            Announcement::updateOrCreate(
                ['slug' => $announcement['slug']],
                [
                    'title' => $announcement['title'],
                    'excerpt' => 'اطلاعیه نمونه برای بررسی بخش اطلاع‌رسانی سایت.',
                    'body' => '<p>متن اطلاعیه نمونه است و توسط مدیر پنل قابل ویرایش خواهد بود.</p>',
                    'category_id' => array_values($categories)[$index % count($categories)]->id,
                    'union_id' => array_values($unions)[$index % count($unions)]->id,
                    'starts_at' => now()->subDay(),
                    'expires_at' => now()->addMonth(),
                    'is_important' => $index < 2,
                    'show_on_home' => true,
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, GuildUnion> $unions */
    private function unionMembers(array $unions): void
    {
        foreach (array_values($unions) as $unionIndex => $union) {
            for ($i = 1; $i <= 2; $i++) {
                UnionMember::updateOrCreate(
                    ['membership_code' => sprintf('G%02d%02d', $unionIndex + 1, $i)],
                    [
                        'union_id' => $union->id,
                        'full_name' => 'عضو نمونه '.$i.' '.$union->name,
                        'national_code' => sprintf('001%07d', ($unionIndex * 10) + $i),
                        'mobile' => sprintf('09112%06d', ($unionIndex * 10) + $i),
                        'phone' => $union->phone,
                        'business_name' => 'واحد صنفی نمونه '.$i,
                        'business_license_number' => sprintf('LIC-%02d-%02d', $unionIndex + 1, $i),
                        'address' => 'گرگان، بازار مرکزی، پلاک نمونه',
                        'status' => 'active',
                        'description' => 'عضو اتحادیه برای نمایش در سامانه.',
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /** @param array<string, PostCategory> $categories */
    private function tourism(User $admin, array $categories): void
    {
        $places = [
            ['title' => 'ناهارخوران گرگان', 'slug' => 'naharkhoran-gorgan'],
            ['title' => 'بازار نعلبندان', 'slug' => 'nalbandan-bazaar'],
            ['title' => 'کاخ موزه گرگان', 'slug' => 'gorgan-palace-museum'],
        ];

        foreach ($places as $index => $place) {
            TourismPlace::updateOrCreate(
                ['slug' => $place['slug']],
                [
                    'title' => $place['title'],
                    'short_description' => 'معرفی مکان گردشگری نمونه برای نمایش در سایت.',
                    'description' => '<p>این مکان به‌عنوان داده نمونه برای بررسی بخش گردشگری ثبت شده است.</p>',
                    'category_id' => $categories['gorgan-tourism']->id,
                    'address' => 'گرگان، استان گلستان',
                    'phone' => '01732220000',
                    'working_hours' => 'همه روزه از ۸ تا ۲۰',
                    'visit_price' => 'رایگان / طبق تعرفه محل',
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, PostCategory> $categories @param array<string, GuildUnion> $unions */
    private function galleries(User $admin, array $categories, array $unions): void
    {
        $galleries = [
            ['title' => 'گزارش تصویری نشست اتحادیه‌ها', 'slug' => 'union-meeting-gallery'],
            ['title' => 'بازدید از بازار گرگان', 'slug' => 'gorgan-market-visit-gallery'],
            ['title' => 'آیین تقدیر از پیشکسوتان اصناف', 'slug' => 'guild-veterans-gallery'],
        ];

        foreach ($galleries as $index => $gallery) {
            Gallery::updateOrCreate(
                ['slug' => $gallery['slug']],
                [
                    'title' => $gallery['title'],
                    'description' => 'گالری نمونه برای تست آرشیو تصاویر و صفحه جزئیات.',
                    'category_id' => $categories['guild-news']->id,
                    'union_id' => array_values($unions)[$index % count($unions)]->id,
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, PostCategory> $categories @param array<string, GuildUnion> $unions */
    private function videos(User $admin, array $categories, array $unions): void
    {
        $videos = [
            ['title' => 'معرفی خدمات اتاق اصناف گرگان', 'slug' => 'gorgan-guild-services-video'],
            ['title' => 'گزارش طرح نظارت بازار', 'slug' => 'market-monitoring-video'],
            ['title' => 'آموزش ثبت درخواست صنفی', 'slug' => 'guild-request-training-video'],
        ];

        foreach ($videos as $index => $video) {
            Video::updateOrCreate(
                ['slug' => $video['slug']],
                [
                    'title' => $video['title'],
                    'description' => 'ویدیوی نمونه برای تست آرشیو ویدیوهای سایت.',
                    'video_type' => 'aparat',
                    'aparat_url' => 'https://www.aparat.com/v/sample',
                    'category_id' => $categories['services-systems']->id,
                    'union_id' => array_values($unions)[$index % count($unions)]->id,
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, PostCategory> $categories */
    private function systems(User $admin, array $categories): void
    {
        $systems = [
            ['title' => 'سامانه ایرانیان اصناف', 'slug' => 'iranianasnaf-system', 'icon' => '💻'],
            ['title' => 'درگاه ملی مجوزها', 'slug' => 'mojavez-system', 'icon' => '📄'],
            ['title' => 'سامانه شکایات و بازرسی', 'slug' => 'inspection-system', 'icon' => '📨'],
        ];

        foreach ($systems as $index => $system) {
            BusinessSystem::updateOrCreate(
                ['slug' => $system['slug']],
                [
                    'title' => $system['title'],
                    'description' => 'سامانه نمونه برای دسترسی سریع کاربران به خدمات صنفی.',
                    'short_description' => 'دسترسی سریع به خدمات و فرایندهای صنفی.',
                    'icon' => $system['icon'],
                    'link' => 'https://example.com',
                    'category_id' => $categories['services-systems']->id,
                    'target' => '_blank',
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, PostCategory> $categories */
    private function electronicServices(User $admin, array $categories): void
    {
        $services = [
            ['title' => 'درخواست صدور پروانه کسب', 'slug' => 'business-license-request', 'icon' => '📝'],
            ['title' => 'تمدید پروانه کسب', 'slug' => 'business-license-renewal', 'icon' => '🔄'],
            ['title' => 'پیگیری درخواست صنفی', 'slug' => 'guild-request-tracking', 'icon' => '🔎'],
        ];

        foreach ($services as $index => $service) {
            ElectronicService::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'title' => $service['title'],
                    'short_description' => 'خدمت الکترونیک نمونه برای تست بخش خدمات.',
                    'body' => '<p>شرح خدمت، مدارک لازم و مسیر دریافت خدمت در این بخش قرار می‌گیرد.</p>',
                    'icon' => $service['icon'],
                    'link' => route('systems.index'),
                    'link_type' => 'internal',
                    'target' => '_self',
                    'category_id' => $categories['services-systems']->id,
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function commissions(User $admin): void
    {
        $commissions = [
            ['title' => 'کمیسیون نظارت', 'slug' => 'supervision-commission'],
            ['title' => 'کمیسیون حل اختلاف', 'slug' => 'dispute-resolution-commission'],
            ['title' => 'کمیسیون آموزش', 'slug' => 'training-commission'],
        ];

        foreach ($commissions as $index => $commissionData) {
            $commission = Commission::updateOrCreate(
                ['slug' => $commissionData['slug']],
                [
                    'title' => $commissionData['title'],
                    'description' => 'کمیسیون نمونه برای تست معرفی کمیسیون‌ها و جلسات مرتبط.',
                    'members' => [['name' => 'عضو نمونه', 'position' => 'نماینده اتاق اصناف']],
                    'attachments' => [],
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            CommissionSession::updateOrCreate(
                ['commission_id' => $commission->id, 'title' => 'جلسه نمونه '.$commission->title],
                [
                    'description' => 'جلسه نمونه برای بررسی نمایش جلسات کمیسیون.',
                    'session_date' => Carbon::now()->subDays($index + 3),
                    'attachments' => [],
                    'images' => [],
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 2),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @param array<string, GuildUnion> $unions */
    private function congratulationMessages(User $admin, array $unions): void
    {
        foreach (array_values($unions) as $index => $union) {
            CongratulationMessage::updateOrCreate(
                ['slug' => 'congratulation-'.$union->slug],
                [
                    'title' => 'پیام تبریک '.$union->display_title,
                    'body' => '<p>پیام تبریک نمونه برای نمایش در صفحه اصلی و صفحه اتحادیه.</p>',
                    'manager_name' => $union->manager_name,
                    'manager_position' => 'رئیس '.$union->name,
                    'union_id' => $union->id,
                    'show_on_home' => true,
                    'show_on_union_page' => true,
                    'status' => 'published',
                    'published_at' => now()->subDays($index + 1),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
