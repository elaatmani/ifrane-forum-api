<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'content' => $this->content,
            'message_type' => $this->message_type,
            'file_url' => $this->getFileUrl(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'sender' => $this->whenLoaded('sender', function() {
                return [
                    'id' => $this->sender->id,
                    'name' => $this->sender->name,
                    'profile_image' => $this->sender->profile_image
                ];
            }),
            
            'conversation' => $this->whenLoaded('conversation', function() {
                return [
                    'id' => $this->conversation->id,
                    'type' => $this->conversation->type,
                    'name' => $this->conversation->name
                ];
            }),
            
            // Computed fields
            'file_name' => $this->when($this->isFile(), function() {
                return basename($this->file_url);
            })
        ];
    }
} 