<?php

namespace App\Http\Resources\Session\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Convert YouTube link to embed format
     */
    private function getEmbedLink(?string $link): ?string
    {
        if (!$link) {
            return null;
        }

        // YouTube video ID patterns
        $patterns = [
            // youtube.com/watch?v=VIDEO_ID
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            // youtu.be/VIDEO_ID
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            // youtube.com/embed/VIDEO_ID
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            // youtube.com/v/VIDEO_ID
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $link, $matches)) {
                $videoId = $matches[1];
                return "https://www.youtube.com/embed/{$videoId}";
            }
        }

        // If it's not a YouTube link, return the original link
        return $link;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        
        // Convert link to embed format if it's a YouTube link
        $data['embed_link'] = $this->getEmbedLink($this->link);
        
        // Add attendees information with count and first 5 attendees
        $attendees = $this->users
        ->where('pivot.role', '!=', 'speaker')
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image
            ];
        });
        
        $data['attendees'] = $attendees->take(3)->values();
        $data['attendees_count'] = $attendees->count();
        $data['speakers'] = $this->users->where('pivot.role', 'speaker')->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image,
            ];
        });
        
        // Add is_joined attribute for current user
        $data['is_joined'] = auth()->check() ? $this->users->contains('id', auth()->id()) : false;
        
        return $data;
    }
}
