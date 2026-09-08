<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            
            // Connects the record to a user and a book
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            
            // Accountability tracking: who facilitated the desk?
            $table->unsignedBigInteger('checked_out_by')->nullable(); // Links to Library Staff/Admin user ID
            $table->unsignedBigInteger('checked_in_by')->nullable();  // Links to Library Staff/Admin user ID
            
            // Timestamps for the checkout workflow
            $table->timestamp('borrowed_at')->useCurrent();
            $table->timestamp('due_at')->nullable(); // System will calculate (Borrowed date + 2 days)
            $table->timestamp('returned_at')->nullable(); // Null until the book is back
            
            // Penalty monitoring
            $table->decimal('penalty_amount', 8, 2)->default(0.00); // 10 Pesos per late day
            $table->string('status')->default('borrowed'); // borrowed, returned, lost, damaged
            
            $table->timestamps();
            
            // Tell the database that checked_out_by and checked_in_by point to the users table
            $table->foreign('checked_out_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_records');
    }
};
