<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #54: users can configure In-App / Email / SMS notification
 * preferences. In-app is always on (no column needed — it's the core
 * feature). These two flags gate the Email and SMS channels specifically.
 * Critical account/security notifications (approval, password reset)
 * ignore these flags and are always sent — see Notifier::MANDATORY_TYPES.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(true)->after('contact_number');
            $table->boolean('sms_notifications')->default(true)->after('email_notifications');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_notifications', 'sms_notifications']);
        });
    }
};
