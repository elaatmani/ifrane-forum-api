<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\ProductVariant\ProductVariantForOrderCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductForOrderCollection extends JsonResource
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
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'is_active' => $this->is_active,
            'quantity' => $this->quantity,
            'video_url' => $this->video_url,
            'store_url' => $this->store_url,
            'image_url' => $this->image_url,
            'variants' => $this->variants,
            // 'variants' => $this->variants->transform(fn($p) => new ProductVariantForOrderCollection($p)),
        ];
    }
}
