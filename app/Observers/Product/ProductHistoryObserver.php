<?php

namespace App\Observers\Product;

use App\Models\Product;
use App\Traits\TrackHistoryTrait;

class ProductHistoryObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->track($product, event: 'created');
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        // You can add specific logic for the updating event if needed
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->track($product, event: 'updated');
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->track($product, event: 'deleted');
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->track($product, event: 'restored');
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->track($product, event: 'force_deleted');
    }
} 