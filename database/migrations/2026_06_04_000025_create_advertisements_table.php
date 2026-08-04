<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('advertisement_positions')->cascadeOnDelete();
            $table->string('title');
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('target')->default('_self');
            $table->timestamp('starts_at')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['position_id', 'is_active', 'starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
