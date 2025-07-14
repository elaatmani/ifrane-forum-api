<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityMemberResource extends JsonResource
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

        $companies = $this->companies;
        $company = $companies->first();

        $company_logo = null;

        if($company) {
            $company_logo = asset('storage/' . $company->logo);
        }

        $badge = ucfirst($this->roles->first()->name);

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
        $mutualConnections = $authUser ? count($authUser->getMutualConnectionsWith($this->id)) : 0;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "profile_image" => $profile_image,
            "role" => $this->roles->first()->name,
            "badge" => $badge,
            "company" => $company ? [
                "id" => $company->id,
                "name" => $company->name,
                "logo" => $company_logo,
            ] : null,
            "connection" => [
                "id" => $connection?->id,
                "status" => $connectionStatus,
                "is_sent_by_me" => $connection && $authUser && $connection->isSentBy($authUser->id),
                "can_connect" => $authUser && $authUser->id !== $this->id && !$connectionStatus,
                "mutual_connections" => $mutualConnections,
            ],
        ];
    }
}
