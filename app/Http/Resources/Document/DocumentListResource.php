<?php

namespace App\Http\Resources\Document;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnail_url = $this->thumbnail_url ? asset('storage/' . $this->thumbnail_url) : null;
        $file_url = $this->file_url ? asset('storage/' . $this->file_url) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail_url' => $thumbnail_url,
            'file_url' => $file_url,
            'type' => $this->type,
            'size' => $this->size,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
