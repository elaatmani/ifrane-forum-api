<?php

namespace App\Http\Resources\Ad;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'product'        => [
                'id'   => $this->product->id,
                'name' => $this->product->name
            ],
            'spend'          => $this->spend,
            'spent_in'     => strtok($this->spent_in, ' ') ?: null,
            'results'     => $this->leads,
            'platform'       => $this->platform,
            'created_at'     => $this->created_at,
        ];
    }
}
