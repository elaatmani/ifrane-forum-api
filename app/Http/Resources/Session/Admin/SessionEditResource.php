<?php

namespace App\Http\Resources\Session\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionEditResource extends JsonResource
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
        $attendees = $this->users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image,
                'role' => $user->pivot->role
            ];
        });
        
        $data['attendees_count'] = $attendees->count();
        $data['attendees'] = $attendees->take(5)->values();
        $data['speakers'] = $this->users->where('pivot.role', 'speaker')->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image,
            ];
        })->values();
        
        // Add is_joined attribute for current user
        $data['is_joined'] = auth()->check() ? $this->users->contains('id', auth()->id()) : false;

        $data['image'] = $this->image ? asset('storage/' . $this->image) : null;
        
        return $data;
    }
}
