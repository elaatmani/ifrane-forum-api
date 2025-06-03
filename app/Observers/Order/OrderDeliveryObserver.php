<?php

namespace App\Observers\Order;

use App\Models\Order;
use App\Events\OrderUpdated;
use App\Services\NawrisService;
use Illuminate\Support\Facades\DB;

class OrderDeliveryObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updating(Order &$order): void
    {
        event(new OrderUpdated($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order &$order): void
    {
        // event(new OrderUpdated($order));
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order delivery event.
     */
    protected function handleOrderEvent(Order $order): void
    {
        // if ($order->delivery_id == NawrisService::id()) {
            // DB::afterCommit(function () use ($order) {
            //     // try {
            //         event(new OrderUpdated($order));
            //     // } catch (\Throwable $th) {
            //     //     DB::rollBack();
            //     //     throw $th;
            //     // }
            // });
        // }
    }
}
