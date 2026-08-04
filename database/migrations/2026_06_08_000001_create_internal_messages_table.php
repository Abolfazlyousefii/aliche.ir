<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->string('type')->default('direct');
            $table->string('priority')->default('normal');
            $table->boolean('allow_reply')->default(true);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('internal_messages')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'read_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['type', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_messages');
    }
};
