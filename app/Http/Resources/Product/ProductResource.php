<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnail_url = $this->thumbnail_url ? asset('storage/' . $this->thumbnail_url) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail_url' => $thumbnail_url,
            'is_bookmarked' => $this->resource->isBookmarked()
        ];
    }
}
