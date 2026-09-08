<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #33: announcements get an optional priority — NORMAL, IMPORTANT, or
 * URGENT. Only IMPORTANT/URGENT should trigger external SMS/email once that
 *'s built (Batch 10) — this column is the flag that later work reads.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
