<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();
            $table->text('message');
            $table->json('recipients');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('send_type')->index();
            $table->string('status')->default('pending')->index();
            $table->json('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'created_at']);
            $table->index(['union_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
