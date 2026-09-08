<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #37: "Library Staff Information" for the About System page — name,
 * position, photo, display order, active status. This is a display-only
 * directory Super Admin curates for public-facing "who works here" info,
 * NOT the same as the `users` table accounts with role=library_staff who
 * actually log in and process circulation. A person can appear in both,
 * a person can appear in neither (committee-only), independently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_staff_directory', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_staff_directory');
    }
};
