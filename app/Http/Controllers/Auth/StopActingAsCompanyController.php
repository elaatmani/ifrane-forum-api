<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StopActingAsCompanyController extends Controller
{
    /**
     * Handle the incoming request to stop acting as a company.
     */
    public function __invoke(Request $request)
    {
        // Check if user is currently acting as a company
        if (!session()->has('acting_as_company')) {
            return response()->json([
                'code' => 'NOT_ACTING_AS_COMPANY',
                'message' => 'You are not currently acting as any company.'
            ], 400);
        }
        
        // Remove company data from session
        session()->forget('acting_as_company');
        
        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Stopped acting as company successfully.'
        ]);
    }
} 