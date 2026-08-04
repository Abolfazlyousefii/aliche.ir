<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('union_types') && ! Schema::hasColumn('union_types', 'image')) {
            Schema::table('union_types', function (Blueprint $table) {
                $table->string('image')->nullable()->after('icon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('union_types') && Schema::hasColumn('union_types', 'image')) {
            Schema::table('union_types', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
