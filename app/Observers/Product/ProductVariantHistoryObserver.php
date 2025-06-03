<?php

namespace App\Observers\Product;

use App\Models\ProductVariant;
use App\Traits\TrackHistoryTrait;

class ProductVariantHistoryObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $variant): void
    {
        $this->track($variant, event: 'created');
    }

    /**
     * Handle the ProductVariant "updating" event.
     */
    public function updating(ProductVariant $variant): void
    {
        // Special monitoring for quantity changes
        $oldAttributes = $variant->getOriginal();
        $newAttributes = $variant->getAttributes();
        
        $oldQuantity = data_get($oldAttributes, 'quantity', 0);
        $newQuantity = data_get($newAttributes, 'quantity', 0);
        
        // Track significant quantity changes
        if ($oldQuantity != $newQuantity) {
            // You can add special notifications or additional tracking here if needed
        }
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $variant): void
    {
        $this->track($variant, event: 'updated');
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $variant): void
    {
        $this->track($variant, event: 'deleted');
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $variant): void
    {
        $this->track($variant, event: 'restored');
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $variant): void
    {
        $this->track($variant, event: 'force_deleted');
    }
} 