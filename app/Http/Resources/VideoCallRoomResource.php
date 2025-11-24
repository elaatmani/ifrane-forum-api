<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoCallRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'room_id' => $this->id,
            'room_url' => $this->room_url,
            'conversation_id' => $this->conversation_id,
            'call_type' => $this->call_type,
            'status' => $this->status,
            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($participant) {
                    return [
                        'user_id' => $participant->user_id,
                        'name' => $participant->user->name ?? 'Unknown',
                        'joined_at' => $participant->joined_at?->toISOString(),
                        'status' => $participant->status
                    ];
                });
            }),
            'whereby_meeting_id' => $this->whereby_meeting_id,
            'host_room_url' => $this->host_room_url,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString()
        ];
    }
}

