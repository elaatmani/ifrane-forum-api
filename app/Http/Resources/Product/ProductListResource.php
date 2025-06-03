<?php

namespace App\Http\Resources\Product;

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

        $quantity = $this->quantity;

        if($this->has_variants) {
            $quantity = $this->variants->sum('quantity');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'is_active' => $this->is_active,
            'quantity' => $quantity,
            'stock_alert' => $this->stock_alert,
            'created_by' => $this->created_by,
            'order_count' => isset($this->additional['order_count']) ? $this->additional['order_count'] : 0,
            'confirmation_rate' => isset($this->additional['confirmation_rate']) ? $this->additional['confirmation_rate'] : 0,
            'delivery_rate' => isset($this->additional['delivery_rate']) ? $this->additional['delivery_rate'] : 0,
            'delivered_quantity' => isset($this->additional['delivered_quantity']) ? $this->additional['delivered_quantity'] : 0,
            'shipped_quantity' => isset($this->additional['shipped_quantity']) ? $this->additional['shipped_quantity'] : 0,
            'available_quantity' => $quantity - $this->additional['delivered_quantity'] - $this->additional['shipped_quantity'],
        ];
    }
}
