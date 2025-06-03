<?php

namespace App\Http\Resources\OrderItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product_name = '';
        $variant_name = '';

        if($this->product_id) {
            $product_name = $this->product->name;
        }

        if($this->product_variant_id) {
            $product_name = $this->product_variant->product->name;
            $variant_name = $this->product_variant->variant_name;
        }


        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $product_name,
            'variant_name' => $variant_name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'updated_at' => $this->updated_at,
        ];
    }
}
