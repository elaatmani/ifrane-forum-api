<?php

namespace App\Http\Resources\Bookmark;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bookmarkable_type' => $this->getSimpleType($this->bookmarkable_type),
            'bookmarkable_id' => $this->bookmarkable_id,
            'bookmarkable' => $this->when(
                $this->bookmarkable,
                fn() => $this->formatBookmarkableData($this->bookmarkable)
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Format the bookmarkable data based on its type.
     */
    private function formatBookmarkableData($bookmarkable)
    {
        if (!$bookmarkable) {
            return null;
        }

        $baseData = [
            'id' => $bookmarkable->id,
            'type' => $this->getSimpleType(get_class($bookmarkable)),
        ];

        // Add type-specific fields
        switch (get_class($bookmarkable)) {
            case 'App\Models\Product':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'description' => $bookmarkable->description,
                    'thumbnail_url' => $bookmarkable->thumbnail_url,
                    'company' => $this->when(
                        $bookmarkable->relationLoaded('company') && $bookmarkable->company,
                        fn() => [
                            'id' => $bookmarkable->company->id,
                            'name' => $bookmarkable->company->name,
                            'logo' => $bookmarkable->company->logo,
                        ]
                    ),
                ]);

            case 'App\Models\Company':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'description' => $bookmarkable->description,
                    'logo' => $bookmarkable->logo,
                    'country' => $this->when(
                        $bookmarkable->relationLoaded('country') && $bookmarkable->country,
                        fn() => [
                            'id' => $bookmarkable->country->id,
                            'name' => $bookmarkable->country->name,
                        ]
                    ),
                ]);

            case 'App\Models\Service':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'description' => $bookmarkable->description,
                    'image' => $bookmarkable->image,
                    'status' => $bookmarkable->status,
                    'company' => $this->when(
                        $bookmarkable->relationLoaded('company') && $bookmarkable->company,
                        fn() => [
                            'id' => $bookmarkable->company->id,
                            'name' => $bookmarkable->company->name,
                            'logo' => $bookmarkable->company->logo,
                        ]
                    ),
                ]);

            case 'App\Models\User':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'email' => $bookmarkable->email,
                    'profile_image' => $bookmarkable->profile_image,
                ]);

            case 'App\Models\Document':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'description' => $bookmarkable->description,
                    'file_url' => $bookmarkable->file_url,
                    'thumbnail_url' => $bookmarkable->thumbnail_url,
                    'type' => $bookmarkable->type,
                    'company' => $this->when(
                        $bookmarkable->relationLoaded('company') && $bookmarkable->company,
                        fn() => [
                            'id' => $bookmarkable->company->id,
                            'name' => $bookmarkable->company->name,
                            'logo' => $bookmarkable->company->logo,
                        ]
                    ),
                ]);

            case 'App\Models\Sponsor':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'description' => $bookmarkable->description,
                    'image' => $bookmarkable->image,
                    'link' => $bookmarkable->link,
                    'is_active' => $bookmarkable->is_active,
                ]);

            case 'App\Models\Category':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'slug' => $bookmarkable->slug,
                    'description' => $bookmarkable->description,
                    'type' => $bookmarkable->type,
                ]);

            case 'App\Models\Certificate':
                return array_merge($baseData, [
                    'name' => $bookmarkable->name,
                    'code' => $bookmarkable->code,
                    'description' => $bookmarkable->description,
                    'type' => $bookmarkable->type,
                    'slug' => $bookmarkable->slug,
                ]);

            default:
                // Fallback for unknown types
                return array_merge($baseData, [
                    'name' => $bookmarkable->name ?? $bookmarkable->title ?? 'Unknown',
                    'description' => $bookmarkable->description ?? null,
                ]);
        }
    }

    /**
     * Get simple type name from full class name.
     */
    private function getSimpleType($fullClassName)
    {
        $classMap = [
            'App\\Models\\Product' => 'product',
            'App\\Models\\Company' => 'company',
            'App\\Models\\Service' => 'service',
            'App\\Models\\User' => 'user',
            'App\\Models\\Document' => 'document',
            'App\\Models\\Sponsor' => 'sponsor',
            'App\\Models\\Category' => 'category',
            'App\\Models\\Certificate' => 'certificate',
        ];

        return $classMap[$fullClassName] ?? strtolower(class_basename($fullClassName));
    }
} 