<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrentSessionDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return response()->json(
            [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "roles" => $user->roles->pluck('name'),
                "role" => $user->roles->first()->name,
            ]
        );
    }
}
