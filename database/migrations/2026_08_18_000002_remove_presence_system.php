<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The online/offline presence system has been removed from LibraSync entirely
 * (see deleted TrackLastSeen middleware, deleted <x-online-status> component,
 * and the removed User::isOnline() helper). This migration safely tears down
 * the last_seen_at column on both fresh installs (where it never existed,
 * since it was removed from 2026_07_20_000001_add_link_and_presence_fields)
 * and existing installs (where that migration already ran and added it).
 *
 * Note: AssistantSession (login_at/logout_at desk-session history) is a
 * separate, legitimate audit/login-history feature and is NOT affected by
 * this migration — only the live "is this user online right now" tracking
 * is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_seen_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_seen_at')->nullable()->after('remember_token');
            });
        }
    }
};
