<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('union_members', function (Blueprint $table): void {
            $table->string('first_name', 90)->nullable()->after('full_name');
            $table->string('last_name', 90)->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::table('union_members', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
