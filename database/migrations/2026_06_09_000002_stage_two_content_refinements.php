<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        foreach ([
            'posts' => 'posts_category_id_foreign',
            'announcements' => 'announcements_category_id_foreign',
            'tourism_places' => 'tourism_places_category_id_foreign',
        ] as $tableName => $foreignName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'category_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                        $table->dropForeign($foreignName);
                    });
                } catch (Throwable) {
                    // Foreign key may not exist in older installations.
                }
            }
        }

        if (Schema::hasTable('announcements') && ! Schema::hasColumn('announcements', 'visibility')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('visibility')->default('public')->after('status')->index();
            });
        }

        if (Schema::hasTable('congratulation_messages')) {
            Schema::table('congratulation_messages', function (Blueprint $table) {
                if (! Schema::hasColumn('congratulation_messages', 'message_type')) {
                    $table->string('message_type')->default('congratulation')->after('union_id')->index();
                }
                if (! Schema::hasColumn('congratulation_messages', 'recipient_type')) {
                    $table->string('recipient_type')->nullable()->after('message_type');
                }
                if (! Schema::hasColumn('congratulation_messages', 'recipient_id')) {
                    $table->unsignedBigInteger('recipient_id')->nullable()->after('recipient_type');
                }
                if (! Schema::hasColumn('congratulation_messages', 'recipient_name')) {
                    $table->string('recipient_name')->nullable()->after('recipient_id');
                }
                if (! Schema::hasColumn('congratulation_messages', 'recipient_mobile')) {
                    $table->string('recipient_mobile')->nullable()->after('recipient_name');
                }
                if (! Schema::hasColumn('congratulation_messages', 'sms_log_id')) {
                    $table->foreignId('sms_log_id')->nullable()->after('recipient_mobile')->constrained('sms_logs')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('tourism_places')) {
            Schema::table('tourism_places', function (Blueprint $table) {
                if (! Schema::hasColumn('tourism_places', 'tourism_type')) {
                    $table->string('tourism_type')->default('nature')->after('category_id')->index();
                }
            });

            if (Schema::hasColumn('tourism_places', 'type')) {
                DB::table('tourism_places')->where('tourism_type', 'shop')->update(['tourism_type' => 'shopping']);
            }
        }

        if (Schema::hasTable('galleries') && ! Schema::hasColumn('galleries', 'display_location')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->string('display_location')->default('both')->after('union_id')->index();
            });
        }

        if (Schema::hasTable('unions')) {
            Schema::table('unions', function (Blueprint $table) {
                if (! Schema::hasColumn('unions', 'price_list_mode')) {
                    $table->string('price_list_mode')->default('table')->after('settings')->index();
                }
                if (! Schema::hasColumn('unions', 'price_list_image')) {
                    $table->string('price_list_image')->nullable()->after('price_list_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unions')) {
            Schema::table('unions', function (Blueprint $table) {
                if (Schema::hasColumn('unions', 'price_list_image')) {
                    $table->dropColumn('price_list_image');
                }
                if (Schema::hasColumn('unions', 'price_list_mode')) {
                    $table->dropColumn('price_list_mode');
                }
            });
        }

        if (Schema::hasTable('galleries') && Schema::hasColumn('galleries', 'display_location')) {
            Schema::table('galleries', fn (Blueprint $table) => $table->dropColumn('display_location'));
        }

        if (Schema::hasTable('tourism_places') && Schema::hasColumn('tourism_places', 'tourism_type')) {
            Schema::table('tourism_places', fn (Blueprint $table) => $table->dropColumn('tourism_type'));
        }

        if (Schema::hasTable('congratulation_messages')) {
            Schema::table('congratulation_messages', function (Blueprint $table) {
                if (Schema::hasColumn('congratulation_messages', 'sms_log_id')) {
                    $table->dropConstrainedForeignId('sms_log_id');
                }
                foreach (['recipient_mobile', 'recipient_name', 'recipient_id', 'recipient_type', 'message_type'] as $column) {
                    if (Schema::hasColumn('congratulation_messages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }


        foreach ([
            'posts' => 'posts_category_id_foreign',
            'announcements' => 'announcements_category_id_foreign',
            'tourism_places' => 'tourism_places_category_id_foreign',
        ] as $tableName => $foreignName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'category_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                        $table->dropForeign($foreignName);
                    });
                } catch (Throwable) {
                    // Foreign key may not exist in older installations.
                }
            }
        }

        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'visibility')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->dropColumn('visibility'));
        }
    }
};
