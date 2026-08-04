<?php

namespace Database\Seeders;

use App\Models\ElectronicService;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $aboutPage = Page::updateOrCreate(
            ['slug' => 'about-gorgan-guild-chamber'],
            [
                'title' => 'درباره اتاق اصناف شهرستان گرگان',
                'excerpt' => 'معرفی مأموریت‌ها، خدمات و ساختار اتاق اصناف شهرستان گرگان.',
                'body' => '<p>اتاق اصناف شهرستان گرگان مرجع هماهنگی، پشتیبانی و نظارت بر فعالیت اتحادیه‌های صنفی شهرستان است و خدمات صنفی، آموزشی و اطلاع‌رسانی را به فعالان اقتصادی و شهروندان ارائه می‌کند.</p>',
                'template' => 'default',
                'status' => 'published',
                'published_at' => now(),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
        $licenseIssueService = ElectronicService::query()->where('slug', 'service-1')->first();
        $licenseRenewalService = ElectronicService::query()->where('slug', 'service-2')->first();

        $menus = [
            'main' => [
                'title' => 'منوی اصلی',
                'items' => [
                    ['title' => 'صفحه اصلی', 'type' => 'home'],
                    ['title' => 'اخبار', 'type' => 'posts_index'],
                    ['title' => 'اطلاعیه‌ها', 'type' => 'announcements_index'],
                    ['title' => 'اتحادیه‌ها', 'type' => 'guilds_index'],
                    ['title' => 'خدمات الکترونیک', 'type' => 'services_index', 'children' => [
                        ['title' => 'ثبت شکایت صنفی', 'type' => 'complaints'],
                        ['title' => 'پیگیری شکایت', 'type' => 'complaints_track'],
                        ['title' => 'صدور پروانه کسب', 'type' => 'service', 'reference' => $licenseIssueService],
                        ['title' => 'تمدید پروانه کسب', 'type' => 'service', 'reference' => $licenseRenewalService],
                        ['title' => 'کمیسیون‌ها', 'type' => 'commissions_index'],
                    ]],
                    ['title' => 'سامانه‌ها', 'type' => 'systems_index'],
                    ['title' => 'گالری تصاویر', 'type' => 'galleries_index'],
                    ['title' => 'گردشگری', 'type' => 'tourism_index'],
                    ['title' => 'تماس با ما', 'type' => 'contact'],
                ],
            ],
            'top' => [
                'title' => 'منوی بالایی',
                'items' => [
                    ['title' => 'ثبت شکایت', 'type' => 'complaints'],
                    ['title' => 'پیگیری شکایت', 'type' => 'complaints_track'],
                    ['title' => 'کمیسیون‌ها', 'type' => 'commissions_index'],
                    ['title' => 'سامانه‌ها', 'type' => 'systems_index'],
                ],
            ],
            'quick' => [
                'title' => 'دسترسی سریع',
                'items' => [
                    ['title' => 'درباره اتاق اصناف', 'type' => $aboutPage ? 'page' : 'home', 'reference' => $aboutPage, 'icon' => '🏛'],
                    ['title' => 'خدمات متقاضیان', 'type' => 'services_index', 'icon' => '📝'],
                    ['title' => 'اتحادیه‌های صنفی', 'type' => 'guilds_index', 'icon' => '🏢'],
                    ['title' => 'ثبت شکایت صنفی', 'type' => 'complaints', 'icon' => '📨'],
                    ['title' => 'پیگیری شکایت', 'type' => 'complaints_track', 'icon' => '🔎'],
                    ['title' => 'اخبار و اطلاعیه‌ها', 'type' => 'posts_index', 'icon' => '📰'],
                    ['title' => 'سامانه‌ها', 'type' => 'systems_index', 'icon' => '💻'],
                    ['title' => 'گردشگری', 'type' => 'tourism_index', 'icon' => '🌳'],
                    ['title' => 'تماس با ما', 'type' => 'contact', 'icon' => '☎️'],
                ],
            ],
            'footer' => [
                'title' => 'منوی فوتر',
                'items' => [
                    ['title' => 'صفحه اصلی', 'type' => 'home'],
                    ['title' => 'آرشیو اخبار', 'type' => 'posts_index'],
                    ['title' => 'اطلاعیه‌ها', 'type' => 'announcements_index'],
                    ['title' => 'اتحادیه‌های صنفی', 'type' => 'guilds_index'],
                    ['title' => 'سامانه خدمات صنفی', 'type' => 'systems_index'],
                    ['title' => 'خدمات الکترونیک', 'type' => 'services_index'],
                    ['title' => 'گالری تصاویر', 'type' => 'galleries_index'],
                    ['title' => 'گردشگری', 'type' => 'tourism_index'],
                    ['title' => 'کمیسیون‌ها', 'type' => 'commissions_index'],
                    ['title' => 'تماس با ما', 'type' => 'contact'],
                ],
            ],
        ];

        foreach ($menus as $location => $menuData) {
            $menu = Menu::updateOrCreate(
                ['location' => $location],
                ['title' => $menuData['title'], 'is_active' => true]
            );

            MenuItem::query()->where('menu_id', $menu->id)->delete();

            foreach ($menuData['items'] as $index => $itemData) {
                $item = $this->createItem($menu, $itemData, $index + 1);

                foreach ($itemData['children'] ?? [] as $childIndex => $childData) {
                    $this->createItem($menu, $childData, $childIndex + 1, $item->id);
                }
            }
        }
    }

    private function createItem(Menu $menu, array $data, int $sortOrder, ?int $parentId = null): MenuItem
    {
        $reference = $data['reference'] ?? null;

        return MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'title' => $data['title'],
            'type' => $data['type'],
            'url' => $data['url'] ?? null,
            'route_name' => null,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'target' => $data['target'] ?? '_self',
            'icon' => $data['icon'] ?? null,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}
