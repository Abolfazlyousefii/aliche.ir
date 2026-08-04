<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `posts` MODIFY `type` ENUM('news', 'article', 'announcement', 'video') NOT NULL DEFAULT 'news'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('posts')->where('type', 'video')->update(['type' => 'news']);
            DB::statement("ALTER TABLE `posts` MODIFY `type` ENUM('news', 'article', 'announcement') NOT NULL DEFAULT 'news'");
        }
    }
};
