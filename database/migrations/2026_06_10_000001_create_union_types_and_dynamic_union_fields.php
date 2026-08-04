<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('description');
            });
        }

        if (! Schema::hasTable('union_types')) {
            Schema::create('union_types', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('unions')) {
            Schema::table('unions', function (Blueprint $table) {
                if (! Schema::hasColumn('unions', 'union_type_id')) {
                    $table->foreignId('union_type_id')->nullable()->after('union_type')->constrained('union_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('unions', 'news_mode')) {
                    $table->string('news_mode')->default('auto')->after('settings');
                }
                if (! Schema::hasColumn('unions', 'president_buttons')) {
                    $table->json('president_buttons')->nullable()->after('news_mode');
                }
            });
        }

        if (! Schema::hasTable('union_selected_posts')) {
            Schema::create('union_selected_posts', function (Blueprint $table) {
                $table->foreignId('union_id')->constrained('unions')->cascadeOnDelete();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->primary(['union_id', 'post_id']);
            });
        }

        $defaults = [
            ['title' => 'اتحادیه‌های تولیدی', 'slug' => 'production', 'icon' => '🏭', 'sort_order' => 10],
            ['title' => 'اتحادیه‌های توزیعی', 'slug' => 'distribution', 'icon' => '🛒', 'sort_order' => 20],
            ['title' => 'اتحادیه‌های خدماتی', 'slug' => 'service', 'icon' => '🧰', 'sort_order' => 30],
            ['title' => 'اتحادیه‌های تخصصی', 'slug' => 'specialized', 'icon' => '🎯', 'sort_order' => 40],
        ];

        foreach ($defaults as $type) {
            DB::table('union_types')->updateOrInsert(
                ['slug' => $type['slug']],
                array_merge($type, ['is_active' => true, 'updated_at' => now(), 'created_at' => now()])
            );
        }

        if (Schema::hasColumn('unions', 'union_type_id') && Schema::hasColumn('unions', 'union_type')) {
            DB::table('unions')->whereNull('union_type_id')->orderBy('id')->chunkById(100, function ($unions) {
                foreach ($unions as $union) {
                    $slug = $union->union_type ?: null;
                    if (! $slug) {
                        continue;
                    }
                    $type = DB::table('union_types')->where('slug', $slug)->first();
                    if (! $type) {
                        $typeId = DB::table('union_types')->insertGetId([
                            'title' => $slug,
                            'slug' => Str::slug($slug) ?: 'union-type-'.$union->id,
                            'sort_order' => 100,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $typeId = $type->id;
                    }
                    DB::table('unions')->where('id', $union->id)->update(['union_type_id' => $typeId]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('union_selected_posts');
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
        if (Schema::hasTable('unions')) {
            Schema::table('unions', function (Blueprint $table) {
                if (Schema::hasColumn('unions', 'union_type_id')) {
                    $table->dropConstrainedForeignId('union_type_id');
                }
                if (Schema::hasColumn('unions', 'news_mode')) {
                    $table->dropColumn('news_mode');
                }
                if (Schema::hasColumn('unions', 'president_buttons')) {
                    $table->dropColumn('president_buttons');
                }
            });
        }
        Schema::dropIfExists('union_types');
    }
};
