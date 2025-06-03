<?php

namespace App\Http\Resources\Sourcing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourcingListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        
        // Include variants if they exist
        $data['variants'] = $this->whenLoaded('variants', function() {
            return $this->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'quantity' => $variant->quantity,
                    'product_variant_id' => $variant->product_variant_id,
                    'sourcing_id' => $variant->sourcing_id,
                    'created_at' => $variant->created_at,
                    'updated_at' => $variant->updated_at
                ];
            });
        });
        
        return $data;
    }
}
