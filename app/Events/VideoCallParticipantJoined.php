<?php

namespace App\Events;

use App\Models\VideoCallRoom;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallParticipantJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(VideoCallRoom $room, User $user)
    {
        $this->room = $room;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->room->conversation_id)
        ];

        // Also broadcast to all participants of the conversation for global notifications
        $conversation = $this->room->conversation;
        if ($conversation) {
            foreach ($conversation->users as $user) {
                $channels[] = new PrivateChannel('user.' . $user->id . '.messages');
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'video_call.participant_joined';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->room->id,
            'conversation_id' => $this->room->conversation_id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'joined_at' => now()->toISOString(),
            'participant_count' => $this->room->getParticipantCount(),
            'call_type' => $this->room->call_type
        ];
    }
}

