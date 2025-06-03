<?php

namespace App\Listeners;

use App\Events\OrderUpdated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderStatusNotification implements ShouldQueue
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
    public function handle(OrderUpdated $event): void
    {
        $order = $event->order;
        
        // Check if agent_status has changed
        if ($order->isDirty('agent_status') && $order->agent_status !== null) {
            $this->notificationService->notifyOrderStatusChange(
                $order,
                'agent_status',
                $order->getOriginal('agent_status'),
                $order->agent_status
            );
        }
        
        // Check if followup_status has changed
        if ($order->isDirty('followup_status') && $order->followup_status !== null) {
            $this->notificationService->notifyOrderStatusChange(
                $order,
                'followup_status',
                $order->getOriginal('followup_status'),
                $order->followup_status
            );
        }
        
        // Check if delivery_status has changed
        if ($order->isDirty('delivery_status') && $order->delivery_status !== null) {
            $this->notificationService->notifyOrderStatusChange(
                $order,
                'delivery_status',
                $order->getOriginal('delivery_status'),
                $order->delivery_status
            );
        }
        
        // Check if agent assignment has changed
        if ($order->isDirty('agent_id') && $order->agent_id !== null) {
            $this->notificationService->notifyAssignment($order, 'agent', auth()->user());
        }
        
        // Check if followup assignment has changed
        if ($order->isDirty('followup_id') && $order->followup_id !== null) {
            $this->notificationService->notifyAssignment($order, 'followup', auth()->user());
        }
        
        // Check if delivery assignment has changed
        if ($order->isDirty('delivery_id') && $order->delivery_id !== null) {
            $this->notificationService->notifyAssignment($order, 'delivery', auth()->user());
        }
    }
} 