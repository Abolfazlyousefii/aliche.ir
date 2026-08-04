<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('union_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('national_code')->nullable()->index();
            $table->string('mobile')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('membership_code')->nullable()->index();
            $table->string('business_name')->nullable();
            $table->string('business_license_number')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'expired'])->default('active');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['union_id', 'status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('union_members');
    }
};
