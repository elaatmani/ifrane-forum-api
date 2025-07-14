<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $colors = ['1abc9c', '3498db', '9b59b6', 'e67e22', 'e74c3c', '34495e', '16a085', '2980b9', '8e44ad', '2c3e50'];

        $index = crc32($this->name) % count($colors);
        $bgColor = $colors[$index];

        $profile_image = $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=ffffff&background=' . $bgColor;


        $company = $this->companies->first();

        $company_logo = $company?->logo;

        if($company_logo) {
            $company_logo = asset('storage/' . $company_logo);
        }

        $authUser = $request->user();
        $connection = $authUser ? $authUser->allConnections()
            ->where(function($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $this->id)
                    ->orWhere('sender_id', $this->id)
                    ->where('receiver_id', $authUser->id);
            })
            ->first() : null;

        $connectionStatus = $connection ? $connection->status : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->roles->first()->name,
            'profile_image' => $profile_image,
            'badge' => ucfirst($this->roles->first()->name),
            'company' => [
                'id' => $company?->id,
                'logo' => $company_logo,
                'name' => $company?->name,
                'role' => $company?->pivot->role,
            ],
            'connection' => [
                'id' => $connection?->id,
                'status' => $connectionStatus,
                'can_connect' => $authUser && $authUser->id !== $this->id && !$connectionStatus,
            ],
        ];
    }
}
