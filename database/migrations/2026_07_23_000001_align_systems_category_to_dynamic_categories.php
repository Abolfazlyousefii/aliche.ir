<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('systems') || ! Schema::hasColumn('systems', 'category_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'systems' AND COLUMN_NAME = 'category_id' AND REFERENCED_TABLE_NAME IS NOT NULL");

            foreach ($constraints as $constraint) {
                DB::statement('ALTER TABLE systems DROP FOREIGN KEY `'.$constraint->CONSTRAINT_NAME.'`');
            }
        }
    }

    public function down(): void
    {
        // The column remains available; restoring legacy post_categories FK could break dynamic categories.
    }
};
