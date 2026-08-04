<?php

namespace Database\Seeders;

use App\Models\AnnouncementCategory;
use App\Models\Permission;
use App\Models\PostCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /** @var array<int, string> */
    private array $basePermissions = [
        'dashboard.view', 'pending_approvals.view',
        'messages.view', 'messages.create', 'messages.send', 'messages.reply', 'messages.delete', 'messages.manage',
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
        'pages.view', 'pages.create', 'pages.edit', 'pages.delete', 'pages.approve', 'pages.publish',
        'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.approve', 'posts.publish', 'posts.unpublish', 'posts.manage_important', 'posts.manage_featured', 'media.view', 'media.upload', 'media.edit', 'media.delete',
        'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete', 'announcements.approve', 'announcements.publish',
        'unions.view', 'unions.create', 'unions.edit', 'unions.delete',
        'union_members.view', 'union_members.create', 'union_members.edit', 'union_members.delete',
        'complaints.view', 'complaints.edit', 'complaints.reply', 'complaints.delete',
        'galleries.view', 'galleries.create', 'galleries.edit', 'galleries.delete', 'galleries.approve', 'galleries.publish',
        'videos.view', 'videos.create', 'videos.edit', 'videos.delete', 'videos.approve', 'videos.publish',
        'tourism.view', 'tourism.create', 'tourism.edit', 'tourism.delete', 'tourism.approve', 'tourism.publish',
        'advertisements.view', 'advertisements.create', 'advertisements.edit', 'advertisements.delete',
        'systems.view', 'systems.create', 'systems.edit', 'systems.delete', 'systems.approve', 'systems.publish',
        'electronic_services.view', 'electronic_services.create', 'electronic_services.edit', 'electronic_services.delete', 'electronic_services.approve', 'electronic_services.publish',
        'commissions.view', 'commissions.create', 'commissions.edit', 'commissions.delete', 'commissions.approve', 'commissions.publish',
        'home_sections.view', 'home_sections.edit',
        'header_settings.view', 'header_settings.edit',
        'footer_settings.view', 'footer_settings.edit',
        'sms.view', 'sms.send', 'sms.logs',
        'contact_messages.view', 'contact_messages.delete',
        'settings.view', 'settings.edit',
        'services.approve', 'services.publish', 'congratulation_messages.view', 'congratulation_messages.create', 'congratulation_messages.edit', 'congratulation_messages.delete', 'congratulation_messages.approve', 'congratulation_messages.publish',
    ];

    public function run(): void
    {
        $permissionIds = collect($this->basePermissions)->mapWithKeys(function (string $name) {
            $permission = Permission::updateOrCreate(
                ['name' => $name],
                [
                    'label' => $this->makeLabel($name),
                    'group' => Str::before($name, '.'),
                    'description' => 'دسترسی پایه '.$this->makeLabel($name),
                ]
            );

            return [$name => $permission->id];
        });

        $superAdmin = Role::updateOrCreate(
            ['name' => 'super-admin'],
            ['label' => 'مدیرکل', 'description' => 'دسترسی کامل به همه بخش‌های پنل مدیریت']
        );
        $superAdmin->permissions()->sync($permissionIds->values()->all());

        $unionExpertPermissions = [
            'dashboard.view',
            'unions.view', 'unions.edit',
            'union_members.view', 'union_members.create', 'union_members.edit', 'union_members.delete',
            'complaints.view', 'complaints.edit', 'complaints.reply',
            'sms.view', 'sms.send', 'sms.logs',
            'messages.view', 'messages.reply', 'messages.delete',
        ];

        $unionExpert = Role::updateOrCreate(
            ['name' => 'union-expert'],
            ['label' => 'کارشناس اتحادیه', 'description' => 'دسترسی محدود به اتحادیه، اعضا، شکایات و پیامک']
        );
        $unionExpert->permissions()->sync($permissionIds->only($unionExpertPermissions)->values()->all());

        if (app()->environment(['local', 'testing'])) {
            $adminUser = User::updateOrCreate(
                ['email' => env('SEED_ADMIN_EMAIL', 'admin@asnaf-gorgan.ir')],
                [
                    'name' => env('SEED_ADMIN_NAME', 'مدیرکل سامانه'),
                    'mobile' => env('SEED_ADMIN_MOBILE', '09110000000'),
                    'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'ChangeMe123!')),
                    'is_active' => true,
                ]
            );
            $adminUser->roles()->syncWithoutDetaching([$superAdmin->id]);

            $expertUser = User::updateOrCreate(
                ['email' => env('SEED_EXPERT_EMAIL', 'expert@asnaf-gorgan.ir')],
                [
                    'name' => env('SEED_EXPERT_NAME', 'کارشناس اتحادیه'),
                    'mobile' => env('SEED_EXPERT_MOBILE', '09110000001'),
                    'password' => Hash::make(env('SEED_EXPERT_PASSWORD', 'ChangeMe123!')),
                    'is_active' => true,
                ]
            );
            $expertUser->roles()->syncWithoutDetaching([$unionExpert->id]);
        }

        foreach ([
            ['title' => 'اخبار', 'slug' => 'news', 'sort_order' => 1],
            ['title' => 'مقالات', 'slug' => 'articles', 'sort_order' => 2],
            ['title' => 'اطلاعیه‌ها', 'slug' => 'announcements', 'sort_order' => 3],
        ] as $category) {
            PostCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['title' => $category['title'], 'sort_order' => $category['sort_order'], 'is_active' => true]
            );
        }

        foreach ([
            ['title' => 'عمومی', 'slug' => 'general', 'sort_order' => 1],
            ['title' => 'فراخوان', 'slug' => 'call', 'sort_order' => 2],
            ['title' => 'بخشنامه', 'slug' => 'directive', 'sort_order' => 3],
        ] as $category) {
            AnnouncementCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['title' => $category['title'], 'sort_order' => $category['sort_order'], 'is_active' => true]
            );
        }
    }

    private function makeLabel(string $permission): string
    {
        [$group, $action] = explode('.', $permission);

        $groups = [
            'dashboard' => 'داشبورد', 'pending_approvals' => 'تایید محتوا', 'users' => 'کاربران', 'roles' => 'نقش‌ها', 'permissions' => 'دسترسی‌ها',
            'menus' => 'منوها', 'pages' => 'صفحات', 'posts' => 'اخبار', 'announcements' => 'اطلاعیه‌ها',
            'unions' => 'اتحادیه‌ها', 'union_members' => 'اعضای اتحادیه‌ها', 'complaints' => 'شکایات',
            'galleries' => 'گالری تصاویر', 'videos' => 'ویدیوها', 'tourism' => 'گردشگری',
            'advertisements' => 'تبلیغات', 'systems' => 'سامانه‌ها', 'electronic_services' => 'خدمات الکترونیک', 'commissions' => 'کمیسیون‌ها',
            'home_sections' => 'تنظیمات صفحه اصلی', 'header_settings' => 'تنظیمات هدر', 'footer_settings' => 'تنظیمات فوتر',
            'sms' => 'پیامک‌ها', 'messages' => 'پیام‌های داخلی', 'contact_messages' => 'پیام‌های تماس', 'settings' => 'تنظیمات سایت',
            'services' => 'خدمات', 'congratulation_messages' => 'پیام‌های تبریک',
        ];

        $actions = [
            'view' => 'مشاهده', 'create' => 'ایجاد', 'edit' => 'ویرایش', 'delete' => 'حذف',
            'approve' => 'تایید', 'publish' => 'انتشار', 'reply' => 'پاسخ', 'send' => 'ارسال', 'logs' => 'گزارش‌ها', 'manage' => 'مدیریت',
        ];

        return ($actions[$action] ?? $action).' '.($groups[$group] ?? $group);
    }
}
