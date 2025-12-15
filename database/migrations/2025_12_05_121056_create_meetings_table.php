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
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('meeting_type', ['member_to_member', 'member_to_company']);
            
            // Use datetime to avoid MySQL TIMESTAMP auto-update-on-update behavior
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('timezone')->default('UTC');
            
            $table->unsignedBigInteger('organizer_id');
            $table->string('organizer_type')->default('user');
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->string('whereby_meeting_id')->nullable();
            $table->string('room_url', 500)->nullable();
            $table->string('host_room_url', 500)->nullable();
            
            $table->enum('status', [
                'pending', 
                'accepted', 
                'declined', 
                'cancelled', 
                'completed', 
                'in_progress'
            ])->default('pending');
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            
            $table->index(['organizer_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index('scheduled_at');
            $table->index('meeting_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
