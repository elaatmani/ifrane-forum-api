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
        Schema::table('sourcings', function (Blueprint $table) {
            $table->double('buying_price')->nullable()->default(0)->after('product_name');
            $table->double('selling_price')->nullable()->default(0)->after('product_name');
            $table->double('weight')->default(0)->nullable()->after('quantity');
            $table->integer('product_id')->nullable()->default(null)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sourcings', function (Blueprint $table) {

            $table->dropColumn('buying_price');
            $table->dropColumn('selling_price');
            $table->dropColumn('product_id');
            $table->dropColumn('weight');
        });
    }
};
