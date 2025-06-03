/**
 * Example implementation of real-time notifications using Laravel Echo and Pusher
 * This would typically be part of your frontend application
 */

// Import Echo and Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Pusher and Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/api/pusher', // This is the endpoint we use for authentication
});

// Notification Manager Class
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.listeners = {
            'onNotificationReceived': [],
            'onNotificationUpdated': [],
            'onNotificationDeleted': [],
            'onCountUpdated': []
        };
    }

    // Initialize notification listeners
    init(userId) {
        this.userId = userId;
        this.setupListeners();
        this.fetchNotifications(); // Initial fetch
    }

    // Setup Echo listeners for real-time updates
    setupListeners() {
        // Subscribe to the private user channel
        const channel = window.Echo.private(`user.${this.userId}`);

        // Listen for new notifications
        channel.listen('.notification.created', (data) => {
            this.handleNewNotification(data);
        });

        // Listen for notification updates (e.g., read status)
        channel.listen('.notification.updated', (data) => {
            this.handleNotificationUpdate(data);
        });

        // Listen for notification deletions
        channel.listen('.notification.deleted', (data) => {
            this.handleNotificationDeletion(data);
        });

        // Listen for unread count updates
        channel.listen('.notification.count', (data) => {
            this.handleCountUpdate(data);
        });
    }

    // Fetch initial notifications from the server
    async fetchNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const data = await response.json();
            this.notifications = data.data;
            
            // Also fetch unread count
            const countResponse = await fetch('/api/notifications/unread-count');
            const countData = await countResponse.json();
            this.unreadCount = countData.count;
            
            // Notify listeners
            this.notifyListeners('onCountUpdated', this.unreadCount);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    }

    // Handle a new notification
    handleNewNotification(notification) {
        // Add to our local collection
        this.notifications.unshift(notification);
        
        // Update unread count if it's unread
        if (notification.unread) {
            this.unreadCount++;
            this.notifyListeners('onCountUpdated', this.unreadCount);
        }
        
        // Notify listeners
        this.notifyListeners('onNotificationReceived', notification);
    }

    // Handle notification update (e.g. read status)
    handleNotificationUpdate(data) {
        const index = this.notifications.findIndex(n => n.id === data.id);
        if (index !== -1) {
            // Update the notification in our collection
            const wasUnread = this.notifications[index].unread;
            const isNowUnread = data.unread;
            
            // Update the notification
            this.notifications[index] = {
                ...this.notifications[index],
                ...data
            };
            
            // Notify listeners
            this.notifyListeners('onNotificationUpdated', this.notifications[index]);
        }
    }

    // Handle notification deletion
    handleNotificationDeletion(data) {
        const index = this.notifications.findIndex(n => n.id === data.id);
        if (index !== -1) {
            // Check if it was unread before removing
            const wasUnread = this.notifications[index].unread;
            
            // Remove from our collection
            const deleted = this.notifications.splice(index, 1)[0];
            
            // Notify listeners
            this.notifyListeners('onNotificationDeleted', deleted);
        }
    }

    // Handle unread count update
    handleCountUpdate(data) {
        this.unreadCount = data.count;
        
        // Notify listeners
        this.notifyListeners('onCountUpdated', this.unreadCount);
    }

    // Mark a notification as read
    async markAsRead(notificationId) {
        try {
            await fetch(`/api/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            // The server will broadcast the update
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    // Mark all notifications as read
    async markAllAsRead() {
        try {
            await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            // The server will broadcast the updates
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    // Delete a notification
    async deleteNotification(notificationId) {
        try {
            await fetch(`/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            // The server will broadcast the deletion
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    }

    // Add an event listener
    addEventListener(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event].push(callback);
        }
    }

    // Remove an event listener
    removeEventListener(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        }
    }

    // Notify all listeners of an event
    notifyListeners(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    }
}

// Export the notification manager
export const notificationManager = new NotificationManager();

// Example usage:
// notificationManager.init(userId); // Initialize with current user ID
// 
// // Add event listeners
// notificationManager.addEventListener('onNotificationReceived', (notification) => {
//     console.log('New notification:', notification);
//     // Update UI
// });
// 
// notificationManager.addEventListener('onCountUpdated', (count) => {
//     console.log('Unread count updated:', count);
//     // Update badge count in UI
// }); 