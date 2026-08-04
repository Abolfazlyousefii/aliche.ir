<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->nullable()->unique()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->foreignId('union_id')->nullable()->after('is_active')->constrained('unions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('union_id');
            $table->dropColumn(['mobile', 'is_active']);
        });
    }
};
