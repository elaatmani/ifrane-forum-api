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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->text('name')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->double('buying_price')->nullable()->default(0);
            $table->double('selling_price')->nullable()->default(0);
            $table->boolean('is_active')->default(1);
            $table->boolean('has_variants')->default(0);
            $table->integer('quantity')->nullable()->default(0);
            $table->text('video_url')->nullable()->default(null);
            $table->text('store_url')->nullable()->default(null);
            $table->text('image_url')->nullable()->default(null);
            $table->integer('created_by')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
