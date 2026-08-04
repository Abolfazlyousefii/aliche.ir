<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('congratulation_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_position')->nullable();
            $table->string('manager_image')->nullable();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();
            $table->boolean('show_on_home')->default(false)->index();
            $table->boolean('show_on_union_page')->default(false)->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['union_id', 'status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('congratulation_messages');
    }
};
