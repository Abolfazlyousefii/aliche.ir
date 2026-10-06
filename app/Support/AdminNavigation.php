<?php

namespace App\Support;

use App\Models\User;

final class AdminNavigation
{
    /**
     * Information architecture of the existing admin routes. Navigation is
     * presentation only: all corresponding writes have server authorization.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'پیشخوان',
                'icon' => 'home',
                'route' => 'admin.dashboard',
                'match' => 'admin.dashboard',
                'permission' => 'dashboard.view',
                'children' => [],
            ],
            [
                'title' => 'مدیریت محتوا',
                'icon' => 'news',
                'children' => [
                    ['title' => 'اخبار و مقاله‌ها', 'route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'permission' => 'posts.view'],
                    ['title' => 'ایجاد خبر', 'route' => 'admin.posts.create', 'match' => 'admin.posts.create', 'permission' => 'posts.create'],
                    ['title' => 'صفحات سایت', 'route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'permission' => 'pages.view'],
                    ['title' => 'اطلاعیه‌ها', 'route' => 'admin.announcements.index', 'match' => 'admin.announcements.*', 'permission' => 'announcements.view'],
                    ['title' => 'پیام‌های مناسبتی', 'route' => 'admin.congratulation_messages.index', 'match' => 'admin.congratulation_messages.*', 'permission' => 'congratulation_messages.view'],
                    ['title' => 'دسته‌بندی اخبار', 'route' => 'admin.categories.index', 'params' => ['type' => 'news'], 'match' => 'admin.categories.*', 'active_type' => 'news', 'permission' => 'posts.view'],
                    ['title' => 'در انتظار تأیید', 'route' => 'admin.pending_approvals.index', 'match' => 'admin.pending_approvals.*', 'permission' => 'pending_approvals.view'],
                ],
            ],
            [
                'title' => 'اتحادیه‌ها و سازمان',
                'icon' => 'building',
                'children' => [
                    ['title' => 'اتحادیه‌ها', 'route' => 'admin.unions.index', 'match' => 'admin.unions.*', 'permission' => 'unions.view'],
                    ['title' => 'اعضای اتحادیه‌ها', 'route' => 'admin.union_members.index', 'match' => 'admin.union_members.*', 'permission' => 'union_members.view'],
                    ['title' => 'انواع اتحادیه', 'route' => 'admin.union-types.index', 'match' => 'admin.union-types.*', 'permission' => 'union_types.view'],
                    ['title' => 'دسته‌بندی اتحادیه‌ها', 'route' => 'admin.categories.index', 'params' => ['type' => 'union'], 'match' => 'admin.categories.*', 'active_type' => 'union', 'permission' => 'unions.view'],
                    ['title' => 'اعضای اتاق اصناف', 'route' => 'admin.chamber_members.index', 'match' => 'admin.chamber_members.*', 'permission' => 'chamber_members.view'],
                    ['title' => 'کمیسیون‌های اتاق', 'route' => 'admin.commissions.index', 'match' => 'admin.commissions.*', 'permission' => 'commissions.view'],
                ],
            ],
            [
                'title' => 'خدمات و گردشگری',
                'icon' => 'tourism',
                'children' => [
                    ['title' => 'خدمات الکترونیکی', 'route' => 'admin.electronic_services.index', 'match' => 'admin.electronic_services.*', 'permission' => 'electronic_services.view'],
                    ['title' => 'سامانه‌ها', 'route' => 'admin.systems.index', 'match' => 'admin.systems.*', 'permission' => 'systems.view'],
                    ['title' => 'مکان‌های گردشگری', 'route' => 'admin.tourism.index', 'match' => 'admin.tourism.*', 'permission' => 'tourism.view'],
                    ['title' => 'دسته‌بندی خدمات', 'route' => 'admin.categories.index', 'params' => ['type' => 'service'], 'match' => 'admin.categories.*', 'active_type' => 'service', 'permission' => 'electronic_services.view'],
                    ['title' => 'دسته‌بندی سامانه‌ها', 'route' => 'admin.categories.index', 'params' => ['type' => 'system'], 'match' => 'admin.categories.*', 'active_type' => 'system', 'permission' => 'systems.view'],
                    ['title' => 'دسته‌بندی گردشگری', 'route' => 'admin.categories.index', 'params' => ['type' => 'tourism'], 'match' => 'admin.categories.*', 'active_type' => 'tourism', 'permission' => 'tourism.view'],
                ],
            ],
            [
                'title' => 'رسانه و تبلیغات',
                'icon' => 'image',
                'children' => [
                    ['title' => 'کتابخانه رسانه', 'route' => 'admin.media.index', 'match' => 'admin.media.*', 'permission' => 'media.view'],
                    ['title' => 'گالری تصاویر', 'route' => 'admin.galleries.index', 'match' => 'admin.galleries.*', 'permission' => 'galleries.view'],
                    ['title' => 'ویدیوها', 'route' => 'admin.videos.index', 'match' => 'admin.videos.*', 'permission' => 'videos.view'],
                    ['title' => 'تبلیغات', 'route' => 'admin.advertisements.index', 'match' => 'admin.advertisements.*', 'permission' => 'advertisements.view'],
                    ['title' => 'جایگاه تبلیغات', 'route' => 'admin.advertisement_positions.index', 'match' => 'admin.advertisement_positions.*', 'permission' => 'advertisements.view'],
                    ['title' => 'دسته‌بندی گالری', 'route' => 'admin.categories.index', 'params' => ['type' => 'gallery'], 'match' => 'admin.categories.*', 'active_type' => 'gallery', 'permission' => 'galleries.view'],
                    ['title' => 'دسته‌بندی ویدیو', 'route' => 'admin.categories.index', 'params' => ['type' => 'video'], 'match' => 'admin.categories.*', 'active_type' => 'video', 'permission' => 'videos.view'],
                ],
            ],
            [
                'title' => 'ارتباطات و پیگیری',
                'icon' => 'mail',
                'badge' => 'unread',
                'children' => [
                    ['title' => 'صندوق ورودی', 'route' => 'admin.messages.inbox', 'match' => 'admin.messages.inbox', 'permission' => null, 'badge' => 'unread'],
                    ['title' => 'پیام‌های ارسالی', 'route' => 'admin.messages.sent', 'match' => 'admin.messages.sent', 'permission' => null],
                    ['title' => 'همه پیام‌های داخلی', 'route' => 'admin.messages.index', 'match' => 'admin.messages.index', 'permission' => 'messages.view'],
                    ['title' => 'ارسال پیام جدید', 'route' => 'admin.messages.create', 'match' => 'admin.messages.create', 'permission' => 'messages.send'],
                    ['title' => 'پیام‌های تماس', 'route' => 'admin.contact_messages.index', 'match' => 'admin.contact_messages.*', 'permission' => 'contact_messages.view'],
                    ['title' => 'شکایات', 'route' => 'admin.complaints.index', 'match' => 'admin.complaints.*', 'permission' => 'complaints.view'],
                    ['title' => 'پیامک‌ها', 'route' => 'admin.sms.index', 'match' => 'admin.sms.*', 'permission' => 'sms.view'],
                ],
            ],
            [
                'title' => 'نمایش و تنظیمات سایت',
                'icon' => 'settings',
                'children' => [
                    ['title' => 'چیدمان صفحه اصلی', 'route' => 'admin.home_sections.index', 'match' => 'admin.home_sections.*', 'permission' => 'home_sections.view'],
                    ['title' => 'منوهای سایت', 'route' => 'admin.menus.index', 'match' => 'admin.menus.*', 'permission' => 'menus.view'],
                    ['title' => 'هدر سایت', 'route' => 'admin.header_settings.edit', 'match' => 'admin.header_settings.*', 'permission' => 'header_settings.view'],
                    ['title' => 'فوتر سایت', 'route' => 'admin.footer_settings.edit', 'match' => 'admin.footer_settings.*', 'permission' => 'footer_settings.view'],
                    ['title' => 'تنظیمات عمومی', 'route' => 'admin.settings.edit', 'match' => 'admin.settings.*', 'permission' => 'settings.view'],
                ],
            ],
            [
                'title' => 'کاربران و سیستم',
                'icon' => 'shield',
                'children' => [
                    ['title' => 'کاربران پنل', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'permission' => 'users.view'],
                    ['title' => 'نقش‌های کاربری', 'route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'permission' => 'roles.view'],
                    ['title' => 'سطوح دسترسی', 'route' => 'admin.permissions.index', 'match' => 'admin.permissions.*', 'permission' => 'permissions.view'],
                ],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function forUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        // Query permissions once for both the sidebar and the quick navigator.
        $roles = $user->roles()->with('permissions:id,name')->get();
        $superAdmin = $roles->contains('name', 'super-admin');
        $permissions = $roles->flatMap(fn ($role) => $role->permissions->pluck('name'))->flip();

        $allowed = static fn (array $item): bool => ! isset($item['permission'])
            || $superAdmin
            || $permissions->has($item['permission']);

        return collect(self::groups())
            ->map(function (array $group) use ($allowed): ?array {
                if (empty($group['children'])) {
                    return $allowed($group) ? $group : null;
                }

                $group['children'] = collect($group['children'])
                    ->filter($allowed)
                    ->values()
                    ->all();

                return $group['children'] !== [] ? $group : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public static function searchableLinks(?User $user): array
    {
        return collect(self::forUser($user))
            ->flatMap(function (array $group): array {
                $items = $group['children'] ?: [$group];

                return array_map(fn (array $item): array => [
                    'group' => $group['title'],
                    'title' => $item['title'],
                    'route' => $item['route'],
                    'params' => $item['params'] ?? [],
                ], $items);
            })
            ->values()
            ->all();
    }
}
