<?php

namespace App\Models;

use App\Notifications\BroadcastableNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class UserNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'notification_type',
        'severity_type',
        'data',
        'read_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($notification) {
            $notification->broadcast();
        });
    }

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is unread.
     *
     * @return bool
     */
    public function isUnread()
    {
        return is_null($this->read_at);
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (!is_null($this->read_at)) {
            $this->update(['read_at' => null]);
        }
    }

    /**
     * Broadcast the notification.
     *
     * @return void
     */
    public function broadcast()
    {
        // Load the user relationship if not already loaded
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        
        // Only broadcast if we have a user
        if ($this->user) {
            // Create a broadcastable notification and broadcast it
            $broadcastable = new BroadcastableNotification($this, $this->user);
            Event::dispatch($broadcastable);
        }
    }
}
