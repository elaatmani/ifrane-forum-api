<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Community\CompanyRecommendationService;

class CompanyShowResource extends JsonResource
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
        
        // Get company recommendations
        $recommendations = [];
        $companyRecommendationService = app(CompanyRecommendationService::class);
        $recommendations = $companyRecommendationService->getRecommendationsForCompany(
            $this->resource, 
            $request->user()
        );
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $logo,
            'background_image' => $background_image,
            'address' => $this->address,
            'description' => $this->description,
            'website' => $this->website,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
            'country' => $this->country->name ?? null,
            'categories' => $this->categories->pluck('name'),
            'certificates' => $this->certificates->pluck('name'),
            'users' => $this->users->map(function($user) {
                return [
                    'name' => $user->name,
                    'role' => $user->pivot->role,
                    'profile_image' => $user->profile_image,
                ];
            }),
            'primary_phone' => $this->primary_phone,
            'secondary_phone' => $this->secondary_phone,
            'primary_email' => $this->primary_email,
            'secondary_email' => $this->secondary_email,
            'recommendations' => $recommendations,
            'is_bookmarked' => $this->resource->isBookmarked()
        ];

    }
}
