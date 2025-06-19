<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyListResource extends JsonResource
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
            'primary_email' => $this->primary_email,
            'primary_phone' => $this->primary_phone,
            'secondary_phone' => $this->secondary_phone,
            'logo' => $logo,
            'background_image' => $background_image,
            'status' => $this->status,
            'country' => $this->country->name,
            'categories' => [
                'count' => $this->categories->count(),
                'items' => $this->categories->take(3)->map(fn($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
            ],
            'certificates' => [
                'count' => $this->certificates->count(),
                'items' => $this->certificates->take(3)->map(fn($certificate) => [
                    'id' => $certificate->id,
                    'name' => $certificate->name,
                    'slug' => $certificate->slug,
                ])
            ],
            'users_count' => $this->users->count(),
            'description' => $this->description,
            'website' => $this->website,
            'streaming_platform' => $this->streaming_platform,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
