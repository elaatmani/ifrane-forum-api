<?php

namespace App\Observers\Order;

use App\Models\OrderItem;
use App\Traits\TrackHistoryTrait;

class OrderItemHistoryObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $this->track($orderItem, event: 'created');
    }

    /**
     * Handle the OrderItem "updating" event.
     */
    public function updating(OrderItem $orderItem): void
    {
        // Special monitoring for quantity or price changes
        $oldAttributes = $orderItem->getOriginal();
        $newAttributes = $orderItem->getAttributes();
        
        $oldQuantity = data_get($oldAttributes, 'quantity', 0);
        $newQuantity = data_get($newAttributes, 'quantity', 0);
        
        $oldPrice = data_get($oldAttributes, 'price', 0);
        $newPrice = data_get($newAttributes, 'price', 0);
        
        // Track significant changes to quantity or price
        if ($oldQuantity != $newQuantity || $oldPrice != $newPrice) {
            // You can add custom notifications or additional tracking here if needed
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        $this->track($orderItem, event: 'updated');
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        $this->track($orderItem, event: 'deleted');
    }

    /**
     * Handle the OrderItem "restored" event.
     */
    public function restored(OrderItem $orderItem): void
    {
        $this->track($orderItem, event: 'restored');
    }

    /**
     * Handle the OrderItem "force deleted" event.
     */
    public function forceDeleted(OrderItem $orderItem): void
    {
        $this->track($orderItem, event: 'force_deleted');
    }
} 