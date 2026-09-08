<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_archives', function (Blueprint $table) {
            $table->id();
            $table->string('semester');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('record_count')->default(0);
            $table->unsignedInteger('user_count')->default(0);
            $table->unsignedInteger('penalty_count')->default(0);
            $table->unsignedInteger('payment_count')->default(0);
            $table->string('zip_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('status')->default('completed'); // completed, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_archives');
    }
};
