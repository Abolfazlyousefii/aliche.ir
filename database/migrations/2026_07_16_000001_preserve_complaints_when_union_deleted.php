<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['union_id']);
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE complaints MODIFY union_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE complaints ALTER COLUMN union_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite accepts NULLs in fresh test databases for this column after the original table rebuilds are skipped.
            // Existing SQLite deployments should rebuild the table manually if they need this historical safeguard.
        } else {
            Schema::table('complaints', function (Blueprint $table) {
                $table->unsignedBigInteger('union_id')->nullable()->change();
            });
        }

        Schema::table('complaints', function (Blueprint $table) {
            $table->foreign('union_id')->references('id')->on('unions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['union_id']);
            $table->foreign('union_id')->references('id')->on('unions')->restrictOnDelete();
        });
    }
};
