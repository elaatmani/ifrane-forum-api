<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewOrderNotification implements ShouldQueue
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $this->notificationService->notifyNewOrder($event->order);
    }
} 