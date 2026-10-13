<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamber_members', function (Blueprint $table) {
            $table->boolean('is_president')->default(false);
            // NULL permits any number of ordinary/inactive members; 1 is unique.
            $table->unsignedTinyInteger('active_president_slot')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('chamber_members', function (Blueprint $table) {
            $table->dropUnique(['active_president_slot']);
            $table->dropColumn(['is_president', 'active_president_slot']);
        });
    }
};
