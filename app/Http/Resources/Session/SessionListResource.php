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

        // Improved date handling with better null checks and type safety
        $startDate = null;
        $startTime = null;
        $endDate = null;
        $endTime = null;

        if ($this->start_date && $this->start_date instanceof \Carbon\Carbon) {
            $startDate = $this->start_date->format('Y-m-d');
            $startTime = $this->start_date->format('H:i');
        }

        if ($this->end_date && $this->end_date instanceof \Carbon\Carbon) {
            $endDate = $this->end_date->format('Y-m-d');
            $endTime = $this->end_date->format('H:i');
        }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "image" => $image,
            "status" => $this->status,
            "start_date" => $startDate,
            "start_time" => $startTime,
            "end_date" => $endDate,
            "end_time" => $endTime,
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
            }),
            "is_joined" => auth()->check() ? $this->users->contains('id', auth()->id()) : false,
        ];
    }
}
