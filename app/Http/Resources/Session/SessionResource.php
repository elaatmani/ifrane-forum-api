<?php

namespace App\Http\Resources\Session;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Convert YouTube link to embed format with start time
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
                $options = 'controls=0&autoplay=1&mute=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&cc_load_policy=0&fs=0&disablekb=1';
                
                // Add start time if session has started
                $startTime = $this->getVideoStartTime();
                if ($startTime > 0) {
                    $options .= "&start={$startTime}";
                }
                
                return "https://www.youtube.com/embed/{$videoId}?{$options}";
            }
        }

        // If it's not a YouTube link, return the original link
        return $link;
    }

    /**
     * Calculate video start time based on session start time
     */
    private function getVideoStartTime(): int
    {
        if (!$this->start_date) {
            return 0;
        }

        $startDate = \Carbon\Carbon::parse($this->start_date);
        $now = \Carbon\Carbon::now();
        
        // If session hasn't started yet, start from beginning
        if ($now->lt($startDate)) {
            return 0;
        }
        
        // Calculate how many seconds have passed since session start
        $secondsPassed = $now->diffInSeconds($startDate);
        
        // Convert to seconds (YouTube start parameter is in seconds)
        return $secondsPassed;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $image = $this->image ? asset('storage/' . $this->image) : null;

        $data['image'] = $image;
        $data['topic'] = $this->topic?->name;
        $data['language'] = $this->language?->name;
        $data['type'] = $this->type?->name;
        
        // Convert link to embed format if it's a YouTube link
        $data['embed_link'] = $this->getEmbedLink($this->link);
        
        // Add session status information
        $data['is_started'] = $this->start_date ? now()->gte($this->start_date) : false;
        $data['is_ended'] = $this->end_date ? now()->gt($this->end_date) : false;
        $data['is_live'] = $data['is_started'] && !$data['is_ended'];
        
        // Add attendees count and speakers for admin list view
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
        })->values();
        
        // Add is_joined attribute for current user
        $data['is_joined'] = auth()->check() ? $this->users->contains('id', auth()->id()) : false;
        
        // Add conversation chat data if user is joined
        if ($data['is_joined'] && auth()->check()) {
            $conversation = $this->conversation;
            if ($conversation) {
                $data['conversation'] = [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->name,
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->updated_at,
                    'participants_count' => $conversation->users()->count(),
                    'unread_count' => $conversation->getUnreadCount(auth()->user()),
                    'last_message' => $conversation->messages()->with('sender')->latest()->first() ? [
                        'id' => $conversation->messages()->latest()->first()->id,
                        'content' => $conversation->messages()->latest()->first()->content,
                        'message_type' => $conversation->messages()->latest()->first()->message_type,
                        'sender_name' => $conversation->messages()->latest()->first()->sender->name ?? null,
                        'created_at' => $conversation->messages()->latest()->first()->created_at
                    ] : null
                ];
            }
        }
     
        return $data;
    }
}
