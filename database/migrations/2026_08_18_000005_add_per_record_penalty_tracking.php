<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penalties previously only lived as a single lump `penalty_balance` on the
 * user and a single `fine_amount`/`fine_status` on each borrow record, with
 * no way to track how much of THAT record's fine had been paid — payments
 * were only ever recorded against the user as a whole. This meant Book A's
 * ₱30 fine and Book B's ₱50 fine couldn't be settled independently, which
 * spec #30 requires (pay Book A -> Book A becomes PAID, Book B stays UNPAID,
 * user balance becomes ₱50).
 *
 * This migration adds:
 *  - borrow_records.fine_paid_amount — how much of *this record's* fine has
 *    been paid so far. fine_status now derives from
 *    fine_paid_amount vs fine_amount (unpaid / partially_paid / paid / waived).
 *  - penalty_payments.borrow_record_id / payment_method — so every payment
 *    is tied to the specific book's penalty it settles (spec #37).
 *
 * Safe on both fresh installs (where the base migrations were already edited
 * to include these columns) and existing installs (where the older versions
 * of those migrations already ran without them).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('borrow_records', 'fine_paid_amount')) {
            Schema::table('borrow_records', function (Blueprint $table) {
                $table->decimal('fine_paid_amount', 8, 2)->default(0.00)->after('fine_amount');
            });
        }

        if (! Schema::hasColumn('penalty_payments', 'borrow_record_id')) {
            Schema::table('penalty_payments', function (Blueprint $table) {
                $table->foreignId('borrow_record_id')->nullable()->after('user_id')
                    ->constrained('borrow_records')->onDelete('cascade');
            });
        }

        if (! Schema::hasColumn('penalty_payments', 'payment_method')) {
            Schema::table('penalty_payments', function (Blueprint $table) {
                $table->string('payment_method')->default('cash')->after('balance_after');
            });
        }
    }

    public function down(): void
    {
        Schema::table('penalty_payments', function (Blueprint $table) {
            if (Schema::hasColumn('penalty_payments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });

        Schema::table('penalty_payments', function (Blueprint $table) {
            if (Schema::hasColumn('penalty_payments', 'borrow_record_id')) {
                $table->dropConstrainedForeignId('borrow_record_id');
            }
        });

        Schema::table('borrow_records', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_records', 'fine_paid_amount')) {
                $table->dropColumn('fine_paid_amount');
            }
        });
    }
};
