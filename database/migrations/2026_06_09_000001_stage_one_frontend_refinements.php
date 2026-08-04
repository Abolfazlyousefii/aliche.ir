<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prices') && ! Schema::hasColumn('prices', 'fetched_at')) {
            Schema::table('prices', function (Blueprint $table) {
                $table->timestamp('fetched_at')->nullable()->after('published_at')->index();
            });
        }

        if (! Schema::hasTable('market_prices')) {
            Schema::create('market_prices', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->default('other')->index();
                $table->decimal('amount', 18, 0)->nullable();
                $table->string('unit')->default('ریال');
                $table->string('source')->nullable();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamp('fetched_at')->nullable()->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('site_settings')) {
            $defaults = [
                ['key' => 'site.site_title', 'group' => 'site', 'value' => 'اتاق اصناف مرکز استان گلستان'],
                ['key' => 'site.default_meta_title', 'group' => 'site', 'value' => 'اتاق اصناف مرکز استان گلستان'],
                ['key' => 'header.top_text', 'group' => 'header', 'value' => 'اتاق اصناف مرکز استان گلستان؛ پشتیبان کسب‌وکارهای صنفی'],
                ['key' => 'header.header_buttons', 'group' => 'header', 'value' => [[
                    'title' => 'سامانه خدمات صنفی',
                    'url' => '/systems',
                    'icon' => '💻',
                    'target' => '_self',
                    'is_active' => true,
                ]]],
                ['key' => 'footer.copyright_text', 'group' => 'footer', 'value' => 'تمام حقوق مادی و معنوی این وبسایت متعلق به اتاق اصناف مرکز استان گلستان می‌باشد'],
            ];

            foreach ($defaults as $setting) {
                DB::table('site_settings')->updateOrInsert(
                    ['key' => $setting['key']],
                    [
                        'group' => $setting['group'],
                        'value' => json_encode($setting['value'], JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('prices') && Schema::hasColumn('prices', 'fetched_at')) {
            Schema::table('prices', function (Blueprint $table) {
                $table->dropIndex(['fetched_at']);
                $table->dropColumn('fetched_at');
            });
        }

        Schema::dropIfExists('market_prices');
    }
};
