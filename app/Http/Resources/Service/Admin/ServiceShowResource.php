<?php

namespace App\Http\Resources\Service\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image_url = $this->image;
        if ($image_url) {
            $image_url = asset('storage/' . $image_url);
        }

        $company_logo_url = $this->company->logo;
        if ($company_logo_url) {
            $company_logo_url = asset('storage/' . $company_logo_url);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $image_url,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name,
            'company_logo_url' => $company_logo_url,
            'status' => $this->status,
            'categories' => $this->categories->map(function($category) {
                return ['id' => $category->id, 'name' => $category->name];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 