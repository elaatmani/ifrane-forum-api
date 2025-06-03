<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\AssignmentNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification about order status change.
     */
    public function notifyOrderStatusChange(Order $order, string $statusType, ?string $oldStatus, string $newStatus)
    {
        try {
            $notification = new OrderStatusNotification($order, $statusType, $oldStatus, $newStatus);
            
            // Determine which users should receive this notification
            $users = $this->getOrderRelatedUsers($order);
            
            foreach ($users as $user) {
                $notification->send($user);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order status notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send a notification about new order creation.
     */
    public function notifyNewOrder(Order $order)
    {
        try {
            $notification = new NewOrderNotification($order);
            
            // Get users who should be notified about new orders
            // This could be admins, managers, or other appropriate roles
            $users = User::role(['admin', 'manager'])->get();
            
            // Also notify the assigned agent if any
            if ($order->agent_id) {
                $users->push($order->agent);
            }
            
            foreach ($users as $user) {
                $notification->send($user);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send new order notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send a notification about order assignment.
     */
    public function notifyAssignment(Order $order, string $assignmentType, ?User $assignedBy = null)
    {
        try {
            $notification = new AssignmentNotification($order, $assignmentType, $assignedBy);
            
            $user = null;
            
            // Determine who to notify based on assignment type
            if ($assignmentType === 'agent' && $order->agent_id) {
                $user = $order->agent;
            } elseif ($assignmentType === 'followup' && $order->followup_id) {
                $user = $order->followup;
            } elseif ($assignmentType === 'delivery' && $order->delivery_id) {
                $user = $order->delivery;
            }
            
            if ($user) {
                $notification->send($user);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send assignment notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send a system notification to specific users or roles.
     */
    public function sendSystemNotification(string $title, string $message, array $data = [], string $severityType = 'info', $users = null)
    {
        try {
            $notification = new SystemNotification($title, $message, $data, $severityType);
            
            // If no specific users provided, send to all admins (only admin role, not manager)
            if (!$users) {
                $users = User::role(['admin'])->get();
            }
            
            $notification->broadcastToUsers($users);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send system notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users related to an order who should receive notifications.
     */
    protected function getOrderRelatedUsers(Order $order)
    {
        $users = collect();
        
        // Add agent if assigned
        if ($order->agent_id) {
            $users->push($order->agent);
        }
        
        // Add followup if assigned
        if ($order->followup_id) {
            $users->push($order->followup);
        }
        
        // Add delivery if assigned
        if ($order->delivery_id) {
            $users->push($order->delivery);
        }
        
        // Add admins (only admin role, not manager)
        $adminUsers = User::role(['admin'])->get();
        $users = $users->merge($adminUsers);
        
        // Remove duplicates
        return $users->unique('id');
    }
    
    /**
     * Send a notification to admins when an order status changes from delivered/settled to something else.
     */
    public function notifyDeliveryStatusRegression(Order $order, string $oldStatus, string $newStatus)
    {
        try {
            // Get only admin users for this special notification
            $adminUsers = User::role(['admin'])->get();
            
            if ($adminUsers->isEmpty()) {
                // If no admins found, try to find user ID 1
                $user = User::find(1);
                if ($user) {
                    $adminUsers = collect([$user]);
                } else {
                    // No admins found, can't send notification
                    return false;
                }
            }
            
            $title = "⚠️ Order #{$order->id} Status Alert";
            $message = "This order was previously delivered or settled.";
            
            // Use system notification with warning severity
            $notification = new SystemNotification(
                $title, 
                $message, 
                [
                    'order' => [
                        'id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ],
                    'type' => 'delivery_regression'
                ],
                'warning'
            );
            
            $notification->broadcastToUsers($adminUsers);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send delivery status regression notification: ' . $e->getMessage());
            return false;
        }
    }
} 