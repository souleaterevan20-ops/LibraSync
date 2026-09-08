<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Clearance feature has been removed from LibraSync entirely (see
 * ClearanceController / ClearanceRequest model, both deleted). This migration
 * safely tears down what it left behind in the database, on both fresh installs
 * (where clearance_requests/is_cleared never existed) and existing installs
 * (where the older create_clearance_requests_table migration already ran).
 *
 * Account reactivation after End of Semester is now handled directly by
 * Super Admin / Library Staff via `is_active` (see UserController::reactivate)
 * and no longer depends on clearance being "completed".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('clearance_requests');

        if (Schema::hasColumn('users', 'is_cleared')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_cleared');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_cleared')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_cleared')->default(true);
            });
        }

        Schema::create('clearance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->string('status')->default('pending');
            $table->boolean('payment_verified')->default(false);
            $table->decimal('remaining_balance', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }
};
