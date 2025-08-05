<?php

namespace App\Http\Resources\Session\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        
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
        
        $data['attendees'] = $attendees->take(3);
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
