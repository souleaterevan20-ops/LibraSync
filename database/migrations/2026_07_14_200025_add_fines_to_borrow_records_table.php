<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->decimal('fine_amount', 8, 2)->default(0.00)->after('status');
            $table->string('fine_status')->default('none')->after('fine_amount'); // none, unpaid, paid
        });
    }

    public function down(): void
    {
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->dropColumn(['fine_amount', 'fine_status']);
        });
    }
};