<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
