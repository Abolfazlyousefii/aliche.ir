<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('home_sections')->insert([
            ['key' => 'hero_slider', 'title' => 'اسلایدر اصلی', 'subtitle' => 'خبرها و پیام‌های مهم اتاق اصناف', 'content' => null, 'settings' => json_encode(['limit' => 6], JSON_UNESCAPED_UNICODE), 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'quick_menu', 'title' => 'دسترسی سریع', 'subtitle' => 'مسیرهای پرتکرار کاربران سایت', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'electronic_services', 'title' => 'خدمات الکترونیک صنفی', 'subtitle' => 'نحوه انجام خدمات و دریافت مجوزها و ثبت درخواست‌ها', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'important_news', 'title' => 'خبرهای مهم', 'subtitle' => 'آخرین خبرهای مهم اتاق اصناف شهرستان گرگان', 'content' => null, 'settings' => json_encode(['limit' => 6], JSON_UNESCAPED_UNICODE), 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'announcements', 'title' => 'اطلاعیه‌ها', 'subtitle' => 'بخشنامه‌ها و اطلاعیه‌های مهم', 'content' => null, 'settings' => json_encode(['limit' => 5], JSON_UNESCAPED_UNICODE), 'is_active' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'unions', 'title' => 'اتحادیه‌های صنفی گرگان', 'subtitle' => 'فهرست منتخب اتحادیه‌های فعال', 'content' => null, 'settings' => json_encode(['limit' => 8], JSON_UNESCAPED_UNICODE), 'is_active' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'advertisements', 'title' => 'تبلیغات', 'subtitle' => 'فضاهای تبلیغاتی صفحه اصلی', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'tourism', 'title' => 'گردشگری گرگان', 'subtitle' => 'جاذبه‌های طبیعی، تاریخی و مراکز خرید', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'commissions', 'title' => 'خدمات و کمیسیون‌های اتاق', 'subtitle' => 'خدمات اصلی، نظارت، آموزش و همکاری‌های صنفی', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 90, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'systems', 'title' => 'سامانه‌ها', 'subtitle' => 'پیوندهای پرکاربرد سامانه‌های صنفی', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'congratulation_messages', 'title' => 'پیام‌های تبریک', 'subtitle' => 'پیام‌ها و مناسبت‌های صنفی', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 110, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'videos', 'title' => 'ویدیوها', 'subtitle' => 'گزارش‌های تصویری و ویدیوهای آموزشی', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 120, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'galleries', 'title' => 'گالری تصاویر', 'subtitle' => 'تصاویر رویدادها و فعالیت‌های اتاق اصناف', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 130, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'contact', 'title' => 'ارتباط با اتاق و دستگاه‌های همکار', 'subtitle' => 'راه‌های ارتباطی و موضوعات قابل پیگیری', 'content' => null, 'settings' => null, 'is_active' => true, 'sort_order' => 140, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
