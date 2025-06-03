<?php

namespace App\Http\Resources\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'is_active' => $this->is_active,
            'role' => Str::title(str_replace('_', ' ', $this->roles->first()->name ?? 'No Role')),
            'permissions' => $this->roles->map->permissions->flatten()->pluck('name'),
            'updated_at' => $this->updated_at,
        ];
    }
}
