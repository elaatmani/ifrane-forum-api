<?php

namespace App\Observers\Order;

use App\Enums\OrderFollowupEnum;
use App\Enums\OrderDeliveryEnum;
use App\Models\Order;
use App\Traits\TrackHistoryTrait;
use App\Services\NotificationService;
use Illuminate\Support\Facades\App;

class OrderHistoryObserver
{

    use TrackHistoryTrait;
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updating(Order $order): void
    {
        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values

        if(data_get($oldAttributes, 'followup_status', null) != data_get($newAttributes, 'followup_status', null) 
        && data_get($newAttributes, 'followup_status', null) == OrderFollowupEnum::RECONFIRMED) {
            $this->track($order, event:'updated');
        }
        
        // Check if delivery status changed from delivered or settled to something else
        $oldDeliveryStatus = data_get($oldAttributes, 'delivery_status', null);
        $newDeliveryStatus = data_get($newAttributes, 'delivery_status', null);
        
        if ($oldDeliveryStatus != $newDeliveryStatus &&
            ($oldDeliveryStatus == OrderDeliveryEnum::DELIVERED->value || 
             $oldDeliveryStatus == OrderDeliveryEnum::SETTLED->value) && 
            $newDeliveryStatus != OrderDeliveryEnum::DELIVERED->value && 
            $newDeliveryStatus != OrderDeliveryEnum::SETTLED->value) {
            
            // Get the notification service
            $notificationService = App::make(NotificationService::class);
            
            // Notify admins about this specific case
            $notificationService->notifyDeliveryStatusRegression($order, $oldDeliveryStatus, $newDeliveryStatus);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $this->track($order, event:'updated');
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
}
