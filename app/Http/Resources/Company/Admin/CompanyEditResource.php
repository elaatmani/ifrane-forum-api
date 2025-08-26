<?php

namespace App\Http\Resources\Company\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logo = $this->logo;
        $background_image = $this->background_image;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'primary_email' => $this->primary_email,
            'secondary_email' => $this->secondary_email,
            'website' => $this->website,
            'streaming_platform' => $this->streaming_platform,
            'country_id' => $this->country_id,
            'primary_phone' => $this->primary_phone,
            'secondary_phone' => $this->secondary_phone,
            'address' => $this->address,
            'description' => $this->description,
            'logo' => $logo,
            'background_image' => $background_image,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
            'users' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            }),
            'categories' => $this->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            }),
            'certificates' => $this->certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'name' => $certificate->name
                ];
            }),
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name
            ] : null,
        ];
    }
}
