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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable()->default(null)->index();
            $table->integer('created_by')->nullable()->default(null)->index();

            $table->string('name')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->string('file_url')->nullable()->default(null);
            $table->string('thumbnail_url')->nullable()->default(null);
            $table->string('type')->nullable()->default(null);
            $table->string('size')->nullable()->default(null);
            $table->string('extension')->nullable()->default(null);
            $table->string('mime_type')->nullable()->default(null);
            $table->enum('status', ['active', 'inactive'])->default('inactive');

            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
