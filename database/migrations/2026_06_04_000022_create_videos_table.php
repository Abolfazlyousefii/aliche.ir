<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('video_type')->default('upload')->index();
            $table->string('video_file')->nullable();
            $table->string('aparat_url')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['status', 'is_active', 'published_at']);
            $table->index(['union_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
