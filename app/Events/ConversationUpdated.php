<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use App\Http\Resources\ConversationResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to all participants of the conversation
        foreach ($this->conversation->users as $user) {
            $channels[] = new PrivateChannel('user.' . $user->id . '.messages');
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        // Load necessary relationships
        $this->conversation->load(['users', 'messages' => function($q) {
            $q->latest()->limit(1);
        }]);

        // Get base conversation data
        $baseData = (new ConversationResource($this->conversation))->resolve();
        
        // Remove the unread_count as it's calculated for the sender
        unset($baseData['unread_count']);
        
        // Add unread count for each participant
        $baseData['unread_counts'] = [];
        foreach ($this->conversation->users as $user) {
            $baseData['unread_counts'][$user->id] = $this->conversation->getUnreadCount($user);
        }
        
        return $baseData;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }



    /**
     * Get the data to broadcast for a specific user.
     */
    public function getDataForUser(User $user): array
    {
        // Load necessary relationships
        $this->conversation->load(['users', 'messages' => function($q) {
            $q->latest()->limit(1);
        }]);

        // Get base conversation data
        $baseData = (new ConversationResource($this->conversation))->resolve();
        
        // Remove the unread_count as it's calculated for the sender
        unset($baseData['unread_count']);
        
        // Add the unread count for this specific user
        $baseData['unread_count'] = $this->conversation->getUnreadCount($user);
        
        return $baseData;
    }
} 