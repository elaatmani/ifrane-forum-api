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
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'session', 'company'])->notNull();
            $table->string('name')->nullable(); // For session/company chats
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['type', 'created_at']);
            $table->index(['session_id', 'type']);
            $table->index(['company_id', 'type']);
            $table->index(['created_by', 'type']);
        });

        // Add foreign key constraints after table creation for better MySQL compatibility
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
}; 