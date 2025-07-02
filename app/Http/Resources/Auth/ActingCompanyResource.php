<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActingCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logo = $this->logo ? asset('storage/' . $this->logo) : null;
        $background_image = $this->background_image ? asset('storage/' . $this->background_image) : null;
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $logo,
            'background_image' => $background_image,
            'role' => $this->pivot->role ?? null,
            'primary_email' => $this->primary_email,
            'website' => $this->website,
            'description' => $this->description,
        ];
    }
} 