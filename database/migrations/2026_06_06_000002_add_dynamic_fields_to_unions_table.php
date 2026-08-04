<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unions', function (Blueprint $table) {
            if (! Schema::hasColumn('unions', 'union_type')) {
                $table->string('union_type')->nullable()->after('manager_name');
            }

            if (! Schema::hasColumn('unions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('union_type')->constrained('categories')->nullOnDelete();
            }

            if (! Schema::hasColumn('unions', 'social_links')) {
                $table->json('social_links')->nullable()->after('working_hours');
            }

            if (! Schema::hasColumn('unions', 'settings')) {
                $table->json('settings')->nullable()->after('social_links');
            }
        });
    }

    public function down(): void
    {
        Schema::table('unions', function (Blueprint $table) {
            if (Schema::hasColumn('unions', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (Schema::hasColumn('unions', 'settings')) {
                $table->dropColumn('settings');
            }
        });
    }
};
