<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewOrderNotification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Create the UserNotification record.
     */
    public function send($user)
    {
        $title = "New Order Created";
        $message = "Order #{$this->order->id} has been created successfully";
        
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'notification_type' => 'new_order',
            'severity_type' => 'info',
            'data' => [
                'order_id' => $this->order->id,
                'customer_name' => $this->order->customer_name,
                'customer_phone' => $this->order->customer_phone,
            ],
        ]);
    }
} 