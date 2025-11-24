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
        Schema::create('video_call_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->string('whereby_meeting_id')->nullable();
            $table->text('room_url')->notNull();
            $table->text('host_room_url')->nullable();
            $table->enum('call_type', ['video', 'voice'])->notNull();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes(); // Add soft delete support

            // Indexes for better performance
            $table->index(['conversation_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['whereby_meeting_id']);
        });

        // Add foreign key constraint after table creation for better MySQL compatibility
        Schema::table('video_call_rooms', function (Blueprint $table) {
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_call_rooms');
    }
};
