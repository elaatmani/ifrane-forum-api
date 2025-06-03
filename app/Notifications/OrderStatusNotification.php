<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderStatusNotification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $statusType;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $statusType, ?string $oldStatus = null, string $newStatus)
    {
        $this->order = $order;
        $this->statusType = $statusType;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the severity type based on status.
     */
    protected function getSeverityType(): string
    {
        $severityMap = [
            'delivered' => 'success',
            'confirmed' => 'success',
            'cancelled' => 'error',
            'returned' => 'warning',
            'default' => 'info',
        ];

        return $severityMap[$this->newStatus] ?? $severityMap['default'];
    }

    /**
     * Create the UserNotification record.
     */
    public function send($user)
    {
        $title = "Order #{$this->order->id} Status Updated";
        $message = "Order #{$this->order->id} status changed from " . 
                  ($this->oldStatus ?: 'unset') . " to {$this->newStatus}";
        
        if ($this->statusType === 'agent_status') {
            $notificationType = 'order_status';
        } elseif ($this->statusType === 'followup_status') {
            $notificationType = 'order_status';
        } elseif ($this->statusType === 'delivery_status') {
            $notificationType = 'order_status';
        } else {
            $notificationType = 'order_status';
        }

        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'notification_type' => $notificationType,
            'severity_type' => $this->getSeverityType(),
            'data' => [
                'order_id' => $this->order->id,
                'status_type' => $this->statusType,
                'new_status' => $this->newStatus,
                'old_status' => $this->oldStatus,
            ],
        ]);
    }
} 