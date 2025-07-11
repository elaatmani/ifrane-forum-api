<?php

namespace App\Http\Resources\Sponsor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SponsorPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "image" => asset('storage/' . $this->image),
            "link" => $this->link,
            "description" => $this->description
        ];
    }
}
