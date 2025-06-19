<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;

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
                'code' => 'SUCCESS',
                'data' => [
                    'user' => new UserResource($user),
                ]
            ]
        );
    }
}
