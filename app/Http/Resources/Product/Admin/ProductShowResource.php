<?php

namespace App\Http\Resources\Product\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $thumbnail_url = $this->thumbnail_url;
        if ($thumbnail_url) {
            $thumbnail_url = asset('storage/' . $thumbnail_url);
        }

        $company_logo_url = $this->company->logo;
        if ($company_logo_url) {
            $company_logo_url = asset('storage/' . $company_logo_url);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail_url' => $thumbnail_url,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name,
            'company_logo_url' => $company_logo_url,
            'categories' => $this->categories->map(function($category) {
                return ['id' => $category->id, 'name' => $category->name];
            }),
            'views' => 0,
            'bookmarks' => 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
                'profile_image' => $this->createdBy->profile_image ? asset('storage/' . $this->createdBy->profile_image) : null,
            ],
        ];
    }
}
