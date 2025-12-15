<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $unreadCount;
    public $conversationId;
    public $totalUnreadCount;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, int $unreadCount, ?string $conversationId = null, ?int $totalUnreadCount = null)
    {
        $this->user = $user;
        $this->unreadCount = $unreadCount;
        $this->conversationId = $conversationId;
        $this->totalUnreadCount = $totalUnreadCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id . '.messages')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'unread.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $data = [
            'user_id' => $this->user->id,
            'count' => $this->unreadCount,
            'conversation_id' => $this->conversationId,
            'updated_at' => now()->toISOString()
        ];

        // Include total unread count if provided
        if ($this->totalUnreadCount !== null) {
            $data['total_unread_count'] = $this->totalUnreadCount;
        }

        return $data;
    }
} 