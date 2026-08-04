<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@example.com')
            ->whereNull('mobile')
            ->update(['mobile' => '09110000000', 'is_active' => true]);

        DB::table('users')
            ->where('email', 'union-expert@example.com')
            ->whereNull('mobile')
            ->update(['mobile' => '09110000001', 'is_active' => true]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@example.com')
            ->where('mobile', '09110000000')
            ->update(['mobile' => null]);

        DB::table('users')
            ->where('email', 'union-expert@example.com')
            ->where('mobile', '09110000001')
            ->update(['mobile' => null]);
    }
};
