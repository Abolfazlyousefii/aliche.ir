<?php

use App\Services\SlugService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('slug_histories')) {
        Schema::create('slug_histories', function (Blueprint $table) {
            $table->id();
            $table->string('sluggable_type');
            $table->unsignedBigInteger('sluggable_id');
            $table->string('old_slug');
            $table->string('new_slug');
            $table->timestamps();
            $table->index(['sluggable_type','old_slug']);
        });
        }
        if (! Schema::hasTable('media')) {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_name'); $table->string('original_name')->nullable(); $table->string('path')->unique();
            $table->string('disk')->default('public'); $table->string('mime_type',100)->nullable(); $table->string('extension',20)->nullable();
            $table->unsignedBigInteger('size')->default(0); $table->unsignedInteger('width')->nullable(); $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable(); $table->string('title')->nullable(); $table->text('caption')->nullable(); $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('hash',64)->nullable()->index(); $table->timestamps();
            $table->index(['mime_type','created_at']);
        });
        }
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts','featured_media_id')) $table->foreignId('featured_media_id')->nullable()->after('featured_image')->constrained('media')->nullOnDelete();
            if (! Schema::hasColumn('posts','featured_order')) $table->unsignedInteger('featured_order')->default(0)->after('is_featured');
        });
        if (! Schema::hasTable('news_media')) {
        Schema::create('news_media', function (Blueprint $table) {
            $table->id(); $table->foreignId('news_id')->constrained('posts')->cascadeOnDelete(); $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); $table->unique(['news_id','media_id']);
        });
        }
        $svc = app(SlugService::class);
        foreach (['posts'=>'title','pages'=>'title','categories'=>'title'] as $table=>$titleCol) {
            if (Schema::hasTable($table) && Schema::hasColumn($table,'slug')) {
                DB::table($table)->orderBy('id')->select('id',$titleCol,'slug')->get()->each(function($row) use($svc,$table,$titleCol){
                    $base = $svc->make($row->slug ?: $row->{$titleCol}, $table.'-'.$row->id); $slug=$base; $i=2;
                    while (DB::table($table)->where('slug',$slug)->where('id','!=',$row->id)->exists()) $slug=$base.'-'.$i++;
                    if ($slug !== $row->slug) DB::table($table)->where('id',$row->id)->update(['slug'=>$slug]);
                });
            }
        }
    }
    public function down(): void
    {
        // Non-destructive rollback: keep imported media, gallery links, and slug history data intact.
        // If a deploy must revert code, leave these additive tables/columns in place to avoid data loss.

    }
};
