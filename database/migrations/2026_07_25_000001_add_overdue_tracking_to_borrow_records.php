<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            // When a Library Staff explicitly flags a loan as overdue (rather than
            // waiting for it to be returned), this records when and how much of the
            // running late fine has already been charged to the borrower's penalty
            // balance — so the eventual check-in doesn't double-charge that amount.
            $table->timestamp('overdue_marked_at')->nullable()->after('fine_status');
            $table->decimal('overdue_charged_amount', 8, 2)->default(0)->after('overdue_marked_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->dropColumn(['overdue_marked_at', 'overdue_charged_amount']);
        });
    }
};
