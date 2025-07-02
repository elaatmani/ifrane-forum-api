<?php

namespace App\Http\Controllers\Auth;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\ActingCompanyResource;

class ActAsCompanyController extends Controller
{
    /**
     * Handle the incoming request to act as a company.
     */
    public function __invoke(Request $request, $companyId)
    {
        $user = $request->user();
        
        // Validate that company exists and is not soft deleted
        $company = Company::whereNull('deleted_at')->find($companyId);
        
        if (!$company) {
            return response()->json([
                'code' => 'COMPANY_NOT_FOUND',
                'message' => 'Company not found or has been deleted.'
            ], 404);
        }
        
        // Check if user belongs to this company
        $userCompanyRelation = $user->companies()
            ->where('company_id', $companyId)
            ->first();
            
        if (!$userCompanyRelation) {
            return response()->json([
                'code' => 'ACCESS_DENIED',
                'message' => 'You do not belong to this company.'
            ], 403);
        }
        
        // Check if user has exhibitor role in this company
        if (!auth()->user()->hasRole('exhibitor')) {
            return response()->json([
                'code' => 'INSUFFICIENT_ROLE',
                'message' => 'You need exhibitor role to act as this company.',
            ], 403);
        }
        
        // Store company data in session
        $companyData = [
            'id' => $company->id,
            'name' => $company->name,
            'role' => $userCompanyRelation->pivot->role,
            'logo' => $company->logo,
            'background_image' => $company->background_image,
            'primary_email' => $company->primary_email,
            'website' => $company->website,
            'description' => $company->description,
        ];
        
        session(['acting_as_company' => $companyData]);
        
        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Now acting as company.',
            'data' => [
                'acting_company' => $companyData
            ]
        ]);
    }
} 