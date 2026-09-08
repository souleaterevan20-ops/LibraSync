<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attachment upload has been removed from the Announcement feature entirely
 * (spec: keep Title/Message/Image/Video/Expiration only — no PDF/Word/Excel
 * attachments). This migration safely drops attachment_path/attachment_name
 * from announcements on both fresh installs (where they never existed, since
 * they were removed from 2026_07_19_000001_create_announcements_table) and
 * existing installs (where that migration already ran and added them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
            if (Schema::hasColumn('announcements', 'attachment_name')) {
                $table->dropColumn('attachment_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'attachment_path')) {
                $table->string('attachment_path')->nullable();
            }
            if (! Schema::hasColumn('announcements', 'attachment_name')) {
                $table->string('attachment_name')->nullable();
            }
        });
    }
};
