<?php

namespace App\Observers\Sourcing;

use App\Models\Sourcing;
use App\Traits\TrackHistoryTrait;

class SourcingHistoryObserver
{
    use TrackHistoryTrait;
    
    /**
     * Handle the Sourcing "created" event.
     */
    public function created(Sourcing $sourcing): void
    {
        //
    }

    /**
     * Handle the Sourcing "updated" event.
     */
    public function updated(Sourcing $sourcing): void
    {
        $this->track($sourcing, event:'updated');
    }

    /**
     * Handle the Sourcing "deleted" event.
     */
    public function deleted(Sourcing $sourcing): void
    {
        //
    }

    /**
     * Handle the Sourcing "restored" event.
     */
    public function restored(Sourcing $sourcing): void
    {
        //
    }

    /**
     * Handle the Sourcing "force deleted" event.
     */
    public function forceDeleted(Sourcing $sourcing): void
    {
        //
    }
}
