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
            $table->foreignId('session_id')->nullable()->constrained('sessions')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['type', 'created_at']);
            $table->index(['session_id', 'type']);
            $table->index(['company_id', 'type']);
            $table->index(['created_by', 'type']);
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