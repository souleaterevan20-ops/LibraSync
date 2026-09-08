<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedInteger('lost_copies')->default(0)->after('available_copies');
            $table->unsignedInteger('damaged_copies')->default(0)->after('lost_copies');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['lost_copies', 'damaged_copies']);
        });
    }
};