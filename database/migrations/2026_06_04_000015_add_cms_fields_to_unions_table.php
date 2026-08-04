<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unions', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('logo')->nullable()->after('slug');
            $table->string('cover_image')->nullable()->after('logo');
            $table->text('short_description')->nullable()->after('description');
            $table->text('address')->nullable()->after('short_description');
            $table->string('phone')->nullable()->after('address');
            $table->string('mobile')->nullable()->after('phone');
            $table->string('email')->nullable()->after('mobile');
            $table->string('website')->nullable()->after('email');
            $table->string('manager_name')->nullable()->after('website');
            $table->string('manager_image')->nullable()->after('manager_name');
            $table->string('working_hours')->nullable()->after('manager_image');
            $table->json('social_links')->nullable()->after('working_hours');
            $table->boolean('complaint_enabled')->default(false)->after('social_links');
            $table->boolean('congratulations_enabled')->default(false)->after('complaint_enabled');
            $table->boolean('news_enabled')->default(true)->after('congratulations_enabled');
            $table->boolean('announcements_enabled')->default(true)->after('news_enabled');
            $table->boolean('gallery_enabled')->default(false)->after('announcements_enabled');
            $table->boolean('videos_enabled')->default(false)->after('gallery_enabled');
            $table->boolean('members_enabled')->default(false)->after('videos_enabled');
            $table->boolean('services_enabled')->default(false)->after('members_enabled');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });

        DB::table('unions')->whereNull('title')->update(['title' => DB::raw('name')]);
    }

    public function down(): void
    {
        Schema::table('unions', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'logo',
                'cover_image',
                'short_description',
                'address',
                'phone',
                'mobile',
                'email',
                'website',
                'manager_name',
                'manager_image',
                'working_hours',
                'social_links',
                'complaint_enabled',
                'congratulations_enabled',
                'news_enabled',
                'announcements_enabled',
                'gallery_enabled',
                'videos_enabled',
                'members_enabled',
                'services_enabled',
                'sort_order',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });
    }
};
