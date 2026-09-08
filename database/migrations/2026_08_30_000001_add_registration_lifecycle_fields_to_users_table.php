<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #77-84: formalizes the account lifecycle without touching the
 * existing is_verified / is_active columns (renaming those would risk
 * breaking every existing query and account). Instead this adds the
 * missing pieces of the lifecycle — who/when a registration was approved,
 * and a distinct REJECTED state that is NOT the same thing as a deleted
 * account — so a registration's full history stays on record.
 *
 * Resulting lifecycle (see User::accountStatus()):
 *   PENDING  -> is_verified = false, rejected_at = null
 *   ACTIVE   -> is_verified = true,  is_active = true
 *   DISABLED -> is_verified = true,  is_active = false
 *   REJECTED -> rejected_at is set (registration never activated)
 *   DELETED  -> soft-deleted (deleted_at is set)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('is_verified')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->string('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
