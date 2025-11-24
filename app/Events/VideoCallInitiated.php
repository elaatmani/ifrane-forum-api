<?php

namespace App\Events;

use App\Models\VideoCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $videoCall;

    /**
     * Create a new event instance.
     */
    public function __construct(VideoCall $videoCall)
    {
        $this->videoCall = $videoCall;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->videoCall->conversation_id)
        ];

        // Also broadcast to all participants of the conversation for global notifications
        $conversation = $this->videoCall->conversation;
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
        return 'video_call.initiated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->videoCall->load(['room', 'conversation', 'initiator']);
        
        return [
            'call_id' => $this->videoCall->id,
            'room_id' => $this->videoCall->room_id,
            'conversation_id' => $this->videoCall->conversation_id,
            'call_type' => $this->videoCall->call_type,
            'status' => $this->videoCall->status,
            'initiated_by' => $this->videoCall->initiated_by,
            'initiator_name' => $this->videoCall->initiator->name ?? 'Unknown',
            'room_url' => $this->videoCall->room->room_url,
            'expires_at' => $this->videoCall->room->expires_at?->toISOString(),
            'created_at' => $this->videoCall->created_at->toISOString()
        ];
    }
}

