<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('unions', 'manager_position')) {
            Schema::table('unions', function (Blueprint $table) {
                $table->string('manager_position', 190)->nullable()->after('manager_name');
            });
        }

        if (! Schema::hasColumn('unions', 'manager_description')) {
            Schema::table('unions', function (Blueprint $table) {
                $table->text('manager_description')->nullable()->after('manager_position');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['manager_position', 'manager_description'],
            fn (string $column) => Schema::hasColumn('unions', $column)
        ));

        if ($columns !== []) {
            Schema::table('unions', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
