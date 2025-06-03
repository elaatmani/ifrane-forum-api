<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastableNotification implements ShouldBroadcast
{
    use SerializesModels;

    protected $userNotification;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserNotification $userNotification, User $user)
    {
        $this->userNotification = $userNotification;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $this->user->id)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->userNotification->id,
            'type' => $this->userNotification->severity_type,
            'title' => $this->userNotification->title,
            'message' => $this->userNotification->message,
            'time' => $this->userNotification->created_at,
            'unread' => $this->userNotification->isUnread(),
            'data' => $this->userNotification->data,
            'notification_type' => $this->userNotification->notification_type
        ];
    }
} 