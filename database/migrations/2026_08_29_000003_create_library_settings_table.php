<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec #34/#35: System Settings — Library Information + Contact Information
 * + Operating Hours, editable by Super Admin. This is a single-row
 * ("singleton") table: there's exactly one library, so one settings row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->string('library_name')->default('LibraSync');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('operating_hours')->nullable();
            $table->timestamps();
        });

        // Seed the single settings row so the app never has to special-case
        // "no settings exist yet."
        \Illuminate\Support\Facades\DB::table('library_settings')->insert([
            'library_name' => 'LibraSync',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('library_settings');
    }
};
