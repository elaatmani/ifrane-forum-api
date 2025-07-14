<?php

namespace App\Http\Resources\Connection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class ConnectionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = auth()->user();
        $otherUser = $this->getOtherUser($currentUser->id);
        $displayStatus = $this->getDisplayStatus();
        
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'responded_at' => $this->responded_at,
            'cancelled_at' => $this->cancelled_at,
            'blocked_at' => $this->blocked_at,
            
            // Display information
            'display_status' => [
                'label' => $displayStatus['label'],
                'description' => $displayStatus['description'],
                'color' => $displayStatus['color'],
            ],
            
            // Connection direction and type
            'is_sent_by_me' => $this->isSentBy($currentUser->id),
            'is_received_by_me' => $this->isReceivedBy($currentUser->id),
            'connection_type' => $this->isSentBy($currentUser->id) ? 'sent' : 'received',
            
            // Other user information
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'profile_image' => $otherUser->profile_image ? asset('storage/' . $otherUser->profile_image) : null,
                'last_action_at' => $otherUser->last_action_at,
                'is_active' => $otherUser->is_active,
            ],
            
            // Sender information (for received requests)
            'sender' => $this->when($this->isReceivedBy($currentUser->id), [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
                'profile_image' => $this->sender->profile_image ? asset('storage/' . $this->sender->profile_image) : null,
                'last_action_at' => $this->sender->last_action_at,
                'is_active' => $this->sender->is_active,
            ]),
            
            // Receiver information (for sent requests)
            'receiver' => $this->when($this->isSentBy($currentUser->id), [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'email' => $this->receiver->email,
                'profile_image' => $this->receiver->profile_image ? asset('storage/' . $this->receiver->profile_image) : null,
                'last_action_at' => $this->receiver->last_action_at,
                'is_active' => $this->receiver->is_active,
            ]),
            
            // Action availability
            'actions' => $this->getAvailableActions($currentUser->id),
            
            // Timing information
            'time_since_created' => $this->created_at->diffForHumans(),
            'time_since_updated' => $this->updated_at->diffForHumans(),
            'time_since_responded' => $this->responded_at ? $this->responded_at->diffForHumans() : null,
            
            // Additional metadata
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get available actions for the current user on this connection.
     */
    private function getAvailableActions($currentUserId): array
    {
        $actions = [];
        
        // If current user is the receiver and status is pending
        if ($this->isReceivedBy($currentUserId) && $this->isPending()) {
            $actions[] = 'accept';
            $actions[] = 'decline';
        }
        
        // If current user is the sender and status is pending
        if ($this->isSentBy($currentUserId) && $this->isPending()) {
            $actions[] = 'cancel';
        }
        
        // If connection is accepted, both users can remove it
        if ($this->isAccepted()) {
            $actions[] = 'remove';
        }
        
        return $actions;
    }
} 