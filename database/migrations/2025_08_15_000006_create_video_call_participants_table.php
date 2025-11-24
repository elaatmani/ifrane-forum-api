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
        Schema::create('video_call_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('room_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->enum('status', ['invited', 'joined', 'left'])->default('invited');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // Ensure unique participant per room
            $table->unique(['room_id', 'user_id'], 'unique_room_user');
            
            // Indexes for better performance
            $table->index(['room_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['joined_at']);
        });

        // Add foreign key constraint after table creation for better MySQL compatibility
        Schema::table('video_call_participants', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('video_call_rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_call_participants');
    }
};
