<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTIVE_SORT_INDEX = 'uct_commission_active_sort_idx';

    public function up(): void
    {
        if (! Schema::hasTable('union_commission_tasks')) {
            Schema::create('union_commission_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('union_commission_id')->constrained('union_commissions')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['union_commission_id', 'is_active', 'sort_order'], self::ACTIVE_SORT_INDEX);
            });

            return;
        }

        if (! $this->indexExists(self::ACTIVE_SORT_INDEX)) {
            Schema::table('union_commission_tasks', function (Blueprint $table) {
                $table->index(['union_commission_id', 'is_active', 'sort_order'], self::ACTIVE_SORT_INDEX);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('union_commission_tasks');
    }

    private function indexExists(string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->whereRaw('table_schema = database()')
                ->where('table_name', 'union_commission_tasks')
                ->where('index_name', $indexName)
                ->exists();
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('union_commission_tasks')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        return false;
    }
};
