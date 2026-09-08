<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Disposal (5-year) feature has been removed from LibraSync entirely
 * (see deleted DisposalController and admin.disposal.* routes/views). This
 * migration safely drops the is_disposed / disposed_at columns from books
 * on both fresh installs (where they never existed, since they were removed
 * from 2026_07_17_000002_add_archive_and_disposal_fields) and existing
 * installs (where that migration already ran and added them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'disposed_at')) {
                $table->dropColumn('disposed_at');
            }
            if (Schema::hasColumn('books', 'is_disposed')) {
                $table->dropColumn('is_disposed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'is_disposed')) {
                $table->boolean('is_disposed')->default(false)->after('replacement_cost');
            }
            if (! Schema::hasColumn('books', 'disposed_at')) {
                $table->timestamp('disposed_at')->nullable()->after('is_disposed');
            }
        });
    }
};
