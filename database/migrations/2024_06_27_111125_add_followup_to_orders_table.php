<?php

use App\Enums\OrderFollowupEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('followup_calls')->after('delivery_status')->default(0)->nullable();
            $table->string('followup_status')->after('delivery_status')->nullable()->default(OrderFollowupEnum::NEW->value);
            $table->integer('followup_id')->after('delivery_status')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('followup_calls');
            $table->dropColumn('followup_status');
            $table->dropColumn('followup_id');
        });
    }
};
