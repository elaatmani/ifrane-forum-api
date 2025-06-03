<?php

namespace App\Http\Resources\GoogleSheet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoogleSheetListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'marketer_id'              => $this->marketer_id,
            'marketer_name'            => $this->marketer?->name,
            'sheet_name'               => $this->sheet_name,
            'sheet_id'                 => $this->sheet_id,
            'name'                     => $this->name,
            'order_count'              => $this->orders()->count(),
            'is_active'                => $this->is_active,
            'has_errors'               => $this->has_errors,
            'created_at'               => $this->created_at,
            'orders_with_errors_count' => $this->orders_with_errors_count,
            'last_synced_at'           => $this->last_synced_at,
        ];
    }
}
