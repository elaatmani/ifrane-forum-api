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
        Schema::create('conversation_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Ensure unique participant per conversation
            $table->unique(['conversation_id', 'user_id'], 'unique_conversation_user');
            
            // Foreign key constraint
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['conversation_id', 'last_read_at']);
            $table->index(['user_id', 'conversation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_users');
    }
}; 