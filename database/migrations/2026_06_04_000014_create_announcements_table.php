<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('announcement_categories')->nullOnDelete();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_important')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['status', 'is_active', 'starts_at', 'expires_at']);
            $table->index(['category_id', 'union_id']);
            $table->index(['is_important', 'show_on_home']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
