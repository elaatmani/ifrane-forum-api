<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{

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
    

} 