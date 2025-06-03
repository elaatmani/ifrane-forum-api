<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $onlyUnread = $request->boolean('unread', false);
        
        $query = Auth::user()->user_notifications()
            ->when($onlyUnread, function ($query) {
                return $query->whereNull('read_at');
            })
            ->orderBy('created_at', 'desc');
            
        $notifications = $query->paginate($perPage);
        
        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }
    
    /**
     * Get the count of unread notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotificationsCount();
        
        return response()->json([
            'count' => $count
        ]);
    }
    
    /**
     * Mark notification as read.
     *
     * @param UserNotification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(UserNotification $notification)
    {
        $this->authorize('update', $notification);
        
        $notification->markAsRead();
        
        // Broadcast the update
        $this->broadcastNotificationUpdate($notification);
        
        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }
    
    /**
     * Mark notification as unread.
     *
     * @param UserNotification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsUnread(UserNotification $notification)
    {
        $this->authorize('update', $notification);
        
        $notification->markAsUnread();
        
        // Broadcast the update
        $this->broadcastNotificationUpdate($notification);
        
        return response()->json([
            'message' => 'Notification marked as unread',
            'notification' => $notification
        ]);
    }
    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        // Get unread notifications
        $notifications = $user->user_notifications()
            ->whereNull('read_at')
            ->get();
            
        // Mark them as read
        $user->user_notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        // Broadcast update for each notification
        foreach ($notifications as $notification) {
            $notification->read_at = now();
            $this->broadcastNotificationUpdate($notification);
        }
        
        // Broadcast unread count update
        $this->broadcastUnreadCount($user);
        
        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
    
    /**
     * Delete a notification.
     *
     * @param UserNotification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(UserNotification $notification)
    {
        $this->authorize('delete', $notification);
        
        $user = Auth::user();
        $notificationId = $notification->id;
        
        $notification->delete();
        
        // Broadcast notification deletion
        $this->broadcastNotificationDeletion($notificationId, $user);
        
        return response()->json([
            'message' => 'Notification deleted'
        ]);
    }
    
    /**
     * Broadcast a notification update.
     *
     * @param UserNotification $notification
     * @return void
     */
    private function broadcastNotificationUpdate(UserNotification $notification)
    {
        $user = $notification->user;
        
        // Create a broadcastable notification event
        event(new \App\Events\NotificationUpdated($notification));
        
        // Also broadcast the updated unread count
        $this->broadcastUnreadCount($user);
    }
    
    /**
     * Broadcast a notification deletion.
     *
     * @param int $notificationId
     * @param User $user
     * @return void
     */
    private function broadcastNotificationDeletion($notificationId, $user)
    {
        // Create a broadcastable deletion event
        event(new \App\Events\NotificationDeleted($notificationId, $user));
        
        // Also broadcast the updated unread count
        $this->broadcastUnreadCount($user);
    }
    
    /**
     * Broadcast the unread count.
     *
     * @param User $user
     * @return void
     */
    private function broadcastUnreadCount($user)
    {
        $count = $user->unreadNotificationsCount();
        
        // Create a broadcastable count event
        event(new \App\Events\NotificationCountUpdated($user, $count));
    }
} 