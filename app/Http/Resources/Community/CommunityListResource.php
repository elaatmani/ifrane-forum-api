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
            'profile_image' => $this->profile_image,
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
                'is_sent_by_me' => $connection && $authUser && $connection->isSentBy($authUser->id),
                'can_connect' => $authUser && $authUser->id !== $this->id && !$connectionStatus,
            ],
        ];
    }
}
