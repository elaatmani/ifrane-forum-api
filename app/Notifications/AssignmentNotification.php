<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignmentNotification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $assignmentType;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $assignmentType, ?User $assignedBy = null)
    {
        $this->order = $order;
        $this->assignmentType = $assignmentType;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Create the UserNotification record.
     */
    public function send($user)
    {
        $assignedByName = $this->assignedBy ? $this->assignedBy->name : 'System';
        
        $title = "New Order Assignment";
        $message = "Order #{$this->order->id} has been assigned to you as " . 
                  ucfirst($this->assignmentType) . " by {$assignedByName}";
        
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'notification_type' => 'assignment',
            'severity_type' => 'info',
            'data' => [
                'order_id' => $this->order->id,
                'assignment_type' => $this->assignmentType,
                'assigned_by' => $this->assignedBy ? $this->assignedBy->id : null,
            ],
        ]);
    }
} 