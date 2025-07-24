<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logo = $this->logo ? asset('storage/' . $this->logo) : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $logo,
            'country' => $this->country->name,
            'is_bookmarked' => $this->resource->isBookmarked()
        ];
    }
}
