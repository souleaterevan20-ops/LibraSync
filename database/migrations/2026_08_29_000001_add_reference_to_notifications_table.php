<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #42/#43: notification architecture — every notification should carry
 * a unique event identity (recipient + event_type + reference_type +
 * reference_id) so the same event can never create a duplicate row, even if
 * a controller action or scheduled command runs twice for the same record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('type');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');

            $table->index(['user_id', 'type', 'reference_type', 'reference_id'], 'notifications_event_identity_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_event_identity_index');
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};
