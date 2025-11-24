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
        Schema::create('video_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('room_id');
            $table->uuid('conversation_id');
            $table->enum('call_type', ['video', 'voice'])->notNull();
            $table->enum('status', ['initiated', 'ringing', 'accepted', 'rejected', 'ended', 'missed'])->default('initiated');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable();
            $table->string('end_reason', 100)->nullable();
            $table->string('reject_reason', 100)->nullable();
            $table->softDeletes(); // Add soft delete support

            // Indexes for better performance
            $table->index(['conversation_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['initiated_by', 'status']);
            $table->index(['accepted_by', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Add foreign key constraints after table creation for better MySQL compatibility
        Schema::table('video_calls', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('video_call_rooms')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};
