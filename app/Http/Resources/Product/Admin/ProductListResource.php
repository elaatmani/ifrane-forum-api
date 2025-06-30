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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'created_at' => $this->created_at,
            'categories' => $this->categories->map(function($category) {
                return ['id' => $category->id, 'name' => $category->name];
            }),
        ];
    }
}
