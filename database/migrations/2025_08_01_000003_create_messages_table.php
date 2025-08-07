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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->notNull();
            $table->enum('message_type', [
                'text', 
                'file', 
                'missed_call', 
                'video_call_request', 
                'voice_call_request', 
                'call_ended', 
                'call_rejected', 
                'call_accepted', 
                'system'
            ])->default('text');
            $table->string('file_url')->nullable();
            $table->json('metadata')->nullable(); // For call duration, call ID, etc.
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['message_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
}; 