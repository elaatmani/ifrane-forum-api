<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'created_by' => $this->created_by,
            'session_id' => $this->session_id,
            'company_id' => $this->company_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'creator' => $this->whenLoaded('creator', function() {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'profile_image' => $this->creator->profile_image
                ];
            }),
            
            'session' => $this->whenLoaded('session', function() {
                return [
                    'id' => $this->session->id,
                    'name' => $this->session->name,
                    'description' => $this->session->description
                ];
            }),
            
            'company' => $this->whenLoaded('company', function() {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                    'logo' => $this->company->logo
                ];
            }),
            
            'users' => $this->whenLoaded('users', function() {
                return $this->users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'profile_image' => $user->profile_image,
                        'last_read_at' => $user->pivot->last_read_at ?? null,
                        'joined_at' => $user->pivot->joined_at ?? null
                    ];
                });
            }),
            
            // 'messages' => $this->whenLoaded('messages', function() {
            //     return $this->messages->map(function($message) {
            //         return [
            //             'id' => $message->id,
            //             'sender_id' => $message->sender_id,
            //             'content' => $message->content,
            //             'message_type' => $message->message_type,
            //             'file_url' => $message->getFileUrl(),
            //             'created_at' => $message->created_at
            //         ];
            //     });
            // }),
            
            // Computed fields
            'other_user' => $this->when($this->isDirect() && $user, function() use ($user) {
                $otherUser = $this->getOtherUser($user);
                return $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'profile_image' => $otherUser->profile_image
                ] : null;
            }),
            
            'unread_count' => $this->when($user, function() use ($user) {
                return $this->getUnreadCount($user);
            }),
            
            'last_message' => (function() {
                // Always get the latest message, even if not loaded
                $lastMessage = $this->messages()->with('sender')->latest()->first();
                
                if (!$lastMessage) {
                    return null;
                }
                
                return [
                    'id' => $lastMessage->id,
                    'content' => $lastMessage->content,
                    'message_type' => $lastMessage->message_type,
                    'sender_name' => $lastMessage->sender->name ?? null,
                    'created_at' => $lastMessage->created_at
                ];
            })()
        ];
    }
} 