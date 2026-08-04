<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('unions', 'union_type')) {
            Schema::table('unions', function (Blueprint $table) {
                $table->string('union_type')->nullable()->after('manager_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('unions', 'union_type')) {
            Schema::table('unions', function (Blueprint $table) {
                $table->dropColumn('union_type');
            });
        }
    }
};
