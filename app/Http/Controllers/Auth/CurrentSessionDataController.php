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
        
        $responseData = [
            'user' => new UserResource($user),
        ];
        
        // Include company context if user is acting as a company
        if (session()->has('acting_as_company')) {
            $responseData['acting_company'] = session('acting_as_company');
        }

        return response()->json(
            [
                'code' => 'SUCCESS',
                'data' => $responseData
            ]
        );
    }
}
