<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_verified'); // false = disabled by Super Admin
            $table->string('avatar')->nullable()->after('is_active');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('favorite_genre')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'avatar', 'bio', 'favorite_genre']);
        });
    }
};
