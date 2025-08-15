<?php

namespace App\Http\Resources\Auth;

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
        $role = ($this->acting_as_role &&$this->hasRole($this->acting_as_role)) ? $this->acting_as_role : $this->roles->first()->name ?? 'No Role';


        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'login_times' => $this->login_times,
            'last_login_at' => $this->last_login_at,
            'is_completed' => $this->is_completed,
            'role' => $role,
            'roles' => $this->roles->pluck('name'),
            'profile_image' => $this->profile_image,
            'permissions' => $this->roles->map->permissions->flatten()->pluck('name'),
        ];
    }
}
