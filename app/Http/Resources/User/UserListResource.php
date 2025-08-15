<?php

namespace App\Http\Resources\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'login_times' => $this->login_times,
            'last_login_at' => $this->last_login_at,
            'last_action_at' => $this->last_action_at,
            'is_active' => $this->is_active,
            'profile_image' => $this->profile_image,
            'role' => Str::title(str_replace('_', ' ', $this->roles->first()->name ?? 'No Role')),
            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => ucfirst($role->name),
                ];
            }),
            'updated_at' => $this->updated_at,
        ];
    }
}
