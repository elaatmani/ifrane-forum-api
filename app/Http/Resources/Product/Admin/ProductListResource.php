<?php

namespace App\Http\Resources\Product\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail_url' => $thumbnail_url,
            'created_at' => $this->created_at,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name,
            'company_logo_url' => $company_logo_url,
            'categories' => $this->categories->map(function($category) {
                return ['id' => $category->id, 'name' => $category->name];
            }),
        ];
    }
}
