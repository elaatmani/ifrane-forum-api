<?php

namespace App\Http\Resources\Connection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConnectionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $displayStatus = $this->getDisplayStatus();
        
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'responded_at' => $this->responded_at,
            
            // Display information
            'display_status' => [
                'label' => $displayStatus['label'],
                'description' => $displayStatus['description'],
                'color' => $displayStatus['color'],
            ],
            
            // Sender information
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
                'profile_image' => $this->sender->profile_image ? asset('storage/' . $this->sender->profile_image) : null,
                'last_action_at' => $this->sender->last_action_at,
                'is_active' => $this->sender->is_active,
            ],
            
            // Receiver information
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'email' => $this->receiver->email,
                'profile_image' => $this->receiver->profile_image ? asset('storage/' . $this->receiver->profile_image) : null,
                'last_action_at' => $this->receiver->last_action_at,
                'is_active' => $this->receiver->is_active,
            ],
            
            // Status checks
            'is_pending' => $this->isPending(),
            'is_accepted' => $this->isAccepted(),
            'is_declined' => $this->isDeclined(),
            'is_cancelled' => $this->isCancelled(),
            'is_blocked' => $this->isBlocked(),
            
            // Timing information
            'time_since_created' => $this->created_at->diffForHumans(),
            'time_since_updated' => $this->updated_at->diffForHumans(),
            'time_since_responded' => $this->responded_at ? $this->responded_at->diffForHumans() : null,
            
            // Additional metadata
            'metadata' => $this->metadata,
        ];
    }
} 