<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoCallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'call_id' => $this->id,
            'room_id' => $this->room_id,
            'conversation_id' => $this->conversation_id,
            'call_type' => $this->call_type,
            'status' => $this->status,
            'participants' => $this->whenLoaded('room', function () {
                return $this->room->participants->map(function ($participant) {
                    return [
                        'user_id' => $participant->user_id,
                        'name' => $participant->user->name ?? 'Unknown',
                        'joined_at' => $participant->joined_at?->toISOString(),
                        'status' => $participant->status
                    ];
                });
            }),
            'initiated_by' => $this->initiated_by,
            'accepted_by' => $this->accepted_by,
            'created_at' => $this->created_at->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'duration' => $this->duration,
            'end_reason' => $this->end_reason,
            'reject_reason' => $this->reject_reason
        ];
    }
}

