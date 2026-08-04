<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'homepage_position')) {
                $table->string('homepage_position', 20)->default('normal')->after('type')->index();
            }
        });

        DB::table('posts')->where('is_top', true)->update(['homepage_position' => 'top']);
        DB::table('posts')->where('is_top', false)->where('is_featured', true)->update(['homepage_position' => 'featured']);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'homepage_position')) {
                $table->dropColumn('homepage_position');
            }
        });
    }
};
