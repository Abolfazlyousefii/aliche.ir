<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['executive_name', 'executive_position', 'executive_image'] as $column) {
            if (! Schema::hasColumn('unions', $column)) {
                Schema::table('unions', function (Blueprint $table) use ($column): void {
                    $table->string($column, $column === 'executive_image' ? 500 : 190)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['executive_image', 'executive_position', 'executive_name'] as $column) {
            if (Schema::hasColumn('unions', $column)) {
                Schema::table('unions', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
