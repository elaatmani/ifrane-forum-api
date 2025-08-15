<?php

namespace App\Http\Resources\Session;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->image ? asset('storage/' . $this->image) : null;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "image" => $image,
            "status" => $this->status,
            "start_date" => explode(' ', $this->start_date)[0],
            "start_time" => explode(' ', $this->start_date)[1],
            "end_date" => explode(' ', $this->end_date)[0],
            "end_time" => explode(' ', $this->end_date)[1],
            "link" => $this->link,
            "topic" => $this->topic?->name,
            "language" => $this->language?->name,
            "type" => $this->type?->name,
            "attendees_count" => $this->users->count(),
            "speakers" => $this->users->where('pivot.role', 'speaker')->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_image' => $user->profile_image,
                ];
            })->values(),
            "is_joined" => auth()->check() ? $this->users->contains('id', auth()->id()) : false,
        ];
    }
}
