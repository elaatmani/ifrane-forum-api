<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role\RoleListResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $roles = Role::all();
        $roles->transform(fn($role) => new RoleListResource($role));

        return response()->json([
            'roles' => $roles,
            'code' => 'SUCCESS',
        ], 200);
    }
}
