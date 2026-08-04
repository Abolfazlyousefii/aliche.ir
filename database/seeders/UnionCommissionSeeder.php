<?php

namespace Database\Seeders;

use App\Models\GuildUnion;
use App\Models\UnionCommission;
use Illuminate\Database\Seeder;

class UnionCommissionSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['کمیسیون نظارت و بازرسی', 'نظارت بر واحدهای صنفی و بررسی گزارش‌های تخلف', ['بازرسی دوره‌ای واحدها', 'تنظیم گزارش تخلف', 'ارجاع پرونده به کمیسیون رسیدگی']],
            ['کمیسیون حل اختلاف', 'رسیدگی اولیه به اختلافات صنفی میان اعضا و مراجعان', ['دریافت درخواست اختلاف', 'دعوت طرفین به جلسه', 'تنظیم گزارش صلح و سازش']],
            ['کمیسیون نرخ‌گذاری', 'بررسی و پیشنهاد نرخ خدمات و اجرت‌های صنفی', ['پایش قیمت‌های بازار', 'بررسی هزینه‌های صنفی', 'اعلام نرخ‌های پیشنهادی']],
            ['کمیسیون آموزش', 'برنامه‌ریزی و اطلاع‌رسانی دوره‌های آموزشی اعضا', ['نیازسنجی آموزشی', 'برگزاری کارگاه‌ها', 'ارزیابی اثربخشی آموزش']],
        ];

        GuildUnion::query()->active()->get()->each(function (GuildUnion $union) use ($templates) {
            foreach ($templates as $index => [$title, $description, $tasks]) {
                $commission = UnionCommission::updateOrCreate(
                    ['union_id' => $union->id, 'title' => $title],
                    [
                        'description' => $description,
                        'icon' => ['🔎', '🤝', '💰', '🎓'][$index] ?? '📋',
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );

                foreach ($tasks as $taskIndex => $taskTitle) {
                    $commission->tasks()->updateOrCreate(
                        ['title' => $taskTitle],
                        [
                            'description' => 'وظیفه تعریف‌شده برای '.$title.' '.$union->display_title,
                            'sort_order' => $taskIndex + 1,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
