<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for columns that are filtered/sorted on frequently — the Returns
 * page (status + due_at), the overdue scheduler (status + due_at), the
 * Penalty Settlement page (fine_amount status filtering), the Archive page
 * (archived_semester), user lookups by role/verification/active status on
 * every login and most admin list pages, and the announcement banner query
 * that runs on all 4 dashboards (spec #67).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_at');
            $table->index('returned_at');
            $table->index('fine_status');
            $table->index('fine_amount');
            $table->index('archived_semester');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_verified');
            $table->index('is_active');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index('posted_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_at']);
            $table->dropIndex(['returned_at']);
            $table->dropIndex(['fine_status']);
            $table->dropIndex(['fine_amount']);
            $table->dropIndex(['archived_semester']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['posted_at']);
            $table->dropIndex(['expires_at']);
        });
    }
};
