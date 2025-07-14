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
        Schema::create('user_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled', 'blocked'])->default('pending');
            $table->text('message');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->json('metadata')->nullable(); // For storing additional connection data
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'sender_id']);
            $table->index(['status', 'created_at']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
            
            // Ensure unique connection between two users (prevent duplicate requests)
            $table->unique(['sender_id', 'receiver_id'], 'unique_connection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_connections');
    }
};
