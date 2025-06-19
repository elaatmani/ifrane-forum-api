<?php

namespace App\Traits\User;

use Spatie\Permission\Models\Role;

trait RoleScope
{
    public function scopeRole($query, $role)
    {
        return $query->whereHas('roles', function($query) use ($role) {
            $query->where('name', $role);
        });
    }
}
