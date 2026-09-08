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
        Schema::create('assistant_logs', function (Blueprint $table) {
            $table->id();
            
            // Connects this log specifically to the Library Staff
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Session tracking
            $table->timestamp('login_at')->useCurrent();
            $table->timestamp('logout_at')->nullable(); // Set when they click log out
            
            // Audit history
            $table->text('processing_history')->nullable(); // Long text description of actions performed
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistant_logs');
    }
};
