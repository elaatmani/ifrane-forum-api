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
        Schema::create('sourcings', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->nullable()->default(null);
            $table->string('product_name')->nullable()->default(null);
            $table->text('product_url')->nullable()->default(null);
            $table->text('quantity')->nullable()->default(null);
            $table->string('destination_country')->nullable()->default(null);
            $table->text('note')->nullable()->default(null);
            $table->string('shipping_method')->nullable()->default(null);
            $table->string('status')->nullable()->default(null);
            $table->float('shipping_cost')->nullable()->default(null);
            $table->float('cost_per_unit')->nullable()->default(0);
            $table->float('additional_fees')->nullable()->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sourcings');
    }
};
