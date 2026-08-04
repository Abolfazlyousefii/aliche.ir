<?php

namespace Database\Seeders;

use App\Models\ChamberMember;
use Illuminate\Database\Seeder;

class ChamberMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['first_name' => 'سید محمد', 'last_name' => 'حسینی', 'position' => 'رئیس اتاق اصناف', 'bio' => 'هماهنگی امور اجرایی، پیگیری مطالبات صنفی و راهبری برنامه‌های کلان اتاق اصناف مرکز استان گلستان.'],
            ['first_name' => 'سید علی', 'last_name' => 'موسوی', 'position' => 'نایب‌رئیس اول', 'bio' => 'پشتیبانی از اتحادیه‌ها، نظارت بر فرآیندهای خدمات صنفی و همراهی با فعالان بازار.'],
            ['first_name' => 'سید رضا', 'last_name' => 'علوی', 'position' => 'نایب‌رئیس دوم', 'bio' => 'پیگیری امور کمیسیون‌ها، جلسات تخصصی و تعامل مستمر با دستگاه‌های همکار.'],
            ['first_name' => 'سید مجتبی', 'last_name' => 'هاشمی', 'position' => 'دبیر اتاق اصناف', 'bio' => 'ساماندهی مکاتبات، اطلاع‌رسانی مصوبات و مدیریت برنامه‌های اداری اتاق اصناف.'],
        ];

        foreach ($members as $index => $member) {
            ChamberMember::updateOrCreate(
                ['first_name' => $member['first_name'], 'last_name' => $member['last_name']],
                $member + [
                    'photo' => 'assets/img/asnaf-gorgan-default.jpg',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
