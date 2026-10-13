<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table): void {
            $table->text('meta_keywords')->nullable();
        });

        Schema::table('videos', function (Blueprint $table): void {
            $table->text('meta_keywords')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table): void {
            $table->dropColumn('meta_keywords');
        });

        Schema::table('galleries', function (Blueprint $table): void {
            $table->dropColumn('meta_keywords');
        });
    }
};
