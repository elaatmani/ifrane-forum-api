<?php

use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderConfirmationEnum;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('google_sheet_id')->nullable()->default(null);
            $table->string('google_sheet_order_id')->nullable()->default(null);
            $table->string('google_sheet_order_date')->nullable()->default(null);

            $table->string('customer_name')->nullable()->default(null);
            $table->string('customer_phone')->nullable()->default(null);
            $table->string('customer_address')->nullable()->default(null);
            $table->string('customer_city')->nullable()->default(null);
            $table->string('customer_area')->nullable()->default(null);
            $table->string('customer_notes')->nullable()->default(null);

            $table->integer('agent_id')->nullable()->default(null);
            $table->string('agent_status')->nullable()->default(OrderConfirmationEnum::NEW->value);
            $table->string('agent_notes')->nullable()->default(null);

            $table->integer('delivery_id')->nullable()->default(null);
            $table->string('delivery_status')->nullable()->default(OrderDeliveryEnum::NOT_SELECTED->value);
            $table->dateTime('order_sent_at')->nullable()->default(null);
            $table->dateTime('order_delivered_at')->nullable()->default(null);
            $table->string('nawris_code')->nullable()->default(null);

            $table->integer('invoice_id')->nullable()->default(null);

            $table->text('return_reason')->nullable()->default(null);

            
            $table->timestamps();
            $table->softDeletes();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
