<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        
        // Broadcast to organizer
        $channels[] = new PrivateChannel('user.' . $this->meeting->organizer_id . '.messages');
        
        // Broadcast to other participants
        foreach ($this->meeting->participants as $participant) {
            if ($participant->user_id !== $this->meeting->organizer_id) {
                $channels[] = new PrivateChannel('user.' . $participant->user_id . '.messages');
            }
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'meeting.started';
    }

    public function broadcastWith(): array
    {
        $this->meeting->load(['organizer', 'user', 'company', 'participants.user']);
        
        return [
            'meeting' => [
                'id' => $this->meeting->id,
                'title' => $this->meeting->title,
                'meeting_type' => $this->meeting->meeting_type,
                'scheduled_at' => $this->meeting->scheduled_at->toISOString(),
                'status' => $this->meeting->status,
                'started_at' => $this->meeting->started_at ? $this->meeting->started_at->toISOString() : null,
                'organizer' => [
                    'id' => $this->meeting->organizer->id,
                    'name' => $this->meeting->organizer->name,
                ],
                'participants' => $this->meeting->participants->map(function($p) {
                    return [
                        'user_id' => $p->user_id,
                        'name' => $p->user->name,
                        'status' => $p->status,
                    ];
                }),
            ],
        ];
    }
}

