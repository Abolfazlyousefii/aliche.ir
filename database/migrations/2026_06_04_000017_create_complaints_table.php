<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('national_code', 20)->nullable();
            $table->string('mobile', 20);
            $table->string('subject');
            $table->text('body');
            $table->string('attachment')->nullable();
            $table->string('status')->default('registered')->index();
            $table->text('admin_response')->nullable();
            $table->text('internal_note')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['union_id', 'status']);
            $table->index(['mobile', 'tracking_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
