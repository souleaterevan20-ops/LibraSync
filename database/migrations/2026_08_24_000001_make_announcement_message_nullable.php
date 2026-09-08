<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The announcement form always treated "message" as optional, but the
 * original migration created the column as NOT NULL — so posting a
 * title-only announcement (no message) threw a database integrity error.
 * This fixes it on any database where the original migration already ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->text('message')->nullable(false)->change();
        });
    }
};