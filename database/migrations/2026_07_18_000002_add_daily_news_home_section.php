<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('home_sections')) {
            return;
        }

        DB::table('home_sections')->updateOrInsert(
            ['key' => 'daily_news'],
            [
                'title' => 'اخبار روزانه',
                'subtitle' => 'خبرهای منتشرشده امروز اتاق اصناف مرکز استان گلستان',
                'content' => null,
                'settings' => json_encode(['limit' => 8], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'sort_order' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('home_sections')) {
            DB::table('home_sections')->where('key', 'daily_news')->delete();
        }
    }
};
