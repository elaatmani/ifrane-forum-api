<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class UserConnection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
        'message',
        'responded_at',
        'cancelled_at',
        'blocked_at',
        'metadata',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'blocked_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Get the user who sent the connection request.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the connection request.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for pending connections.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for accepted connections.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope for declined connections.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }

    /**
     * Scope for cancelled connections.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for blocked connections.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }

    /**
     * Scope for connections involving a specific user (as sender or receiver).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        });
    }

    /**
     * Scope for connections sent by a specific user.
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope for connections received by a specific user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    /**
     * Scope for connections between two specific users.
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }

    /**
     * Check if the connection is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the connection is accepted.
     */
    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if the connection is declined.
     */
    public function isDeclined()
    {
        return $this->status === self::STATUS_DECLINED;
    }

    /**
     * Check if the connection is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the connection is blocked.
     */
    public function isBlocked()
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /**
     * Accept the connection request.
     */
    public function accept()
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        return $this;
    }

    /**
     * Decline the connection request.
     */
    public function decline()
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        return $this;
    }

    /**
     * Cancel the connection request.
     */
    public function cancel()
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return $this;
    }

    /**
     * Block the connection.
     */
    public function block()
    {
        $this->update([
            'status' => self::STATUS_BLOCKED,
            'blocked_at' => now(),
        ]);

        return $this;
    }

    /**
     * Get the other user in the connection (not the current user).
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->sender_id == $currentUserId) {
            return $this->receiver;
        } elseif ($this->receiver_id == $currentUserId) {
            return $this->sender;
        }

        return null;
    }

    /**
     * Check if the current user is the sender of the connection.
     */
    public function isSentBy($userId)
    {
        return $this->sender_id == $userId;
    }

    /**
     * Check if the current user is the receiver of the connection.
     */
    public function isReceivedBy($userId)
    {
        return $this->receiver_id == $userId;
    }

    /**
     * Get the display status based on configuration.
     */
    public function getDisplayStatus()
    {
        $statuses = Config::get('connections.statuses', []);
        return $statuses[$this->status] ?? [
            'label' => ucfirst($this->status),
            'description' => 'Connection status',
            'color' => 'secondary',
        ];
    }

    /**
     * Check if notifications should be sent for this connection event.
     */
    public function shouldSendNotification($eventType)
    {
        $notificationConfig = Config::get('connections.notifications.events.' . $eventType, []);
        return Config::get('connections.notifications.enabled', true) && 
               ($notificationConfig['enabled'] ?? false);
    }

    /**
     * Get the notification message template for an event.
     */
    public function getNotificationTemplate($eventType)
    {
        return Config::get('connections.notifications.events.' . $eventType . '.message_template', '');
    }

    /**
     * Get the notification data for broadcasting.
     */
    public function getNotificationData($eventType)
    {
        return [
            'connection_id' => $this->id,
            'event_type' => $eventType,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
                'profile_image' => $this->sender->profile_image,
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'email' => $this->receiver->email,
                'profile_image' => $this->receiver->profile_image,
            ],
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'responded_at' => $this->responded_at,
        ];
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function booted()
    {
        // You can add model events here if needed
        // For example, to automatically send notifications when status changes
    }
}
