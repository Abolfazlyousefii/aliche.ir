<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('union_rules')) {
            Schema::create('union_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('file')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['union_id', 'is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('union_minutes')) {
            Schema::create('union_minutes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
                $table->string('title');
                $table->date('meeting_date')->nullable();
                $table->string('file')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['union_id', 'is_active', 'meeting_date']);
            });
        }

        if (! Schema::hasTable('union_educations')) {
            Schema::create('union_educations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('link')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['union_id', 'is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('union_prices')) {
            Schema::create('union_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
                $table->string('title');
                $table->decimal('price', 20, 2)->nullable();
                $table->string('currency')->default('ریال');
                $table->string('type')->nullable();
                $table->date('updated_on')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['union_id', 'is_active', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('union_prices');
        Schema::dropIfExists('union_educations');
        Schema::dropIfExists('union_minutes');
        Schema::dropIfExists('union_rules');
    }
};
