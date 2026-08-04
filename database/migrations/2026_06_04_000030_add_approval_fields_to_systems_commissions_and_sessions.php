<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('target')->index();
            $table->timestamp('published_at')->nullable()->after('status')->index();
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable()->after('approved_by');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('status')->index();
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable()->after('approved_by');
        });

        Schema::table('commission_sessions', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('commission_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('rejected_reason');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['published_at', 'rejected_reason']);
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['status', 'published_at', 'rejected_reason']);
        });
    }
};
