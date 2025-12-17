<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class MyCompanyMembersController extends Controller
{
    /**
     * Get all members of the company.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        // Check if user is acting as company
        $actingCompany = session('acting_as_company');
        if (!$actingCompany) {
            return response()->json([
                'code' => 'NOT_ACTING_AS_COMPANY',
                'message' => 'You must be acting as a company to access this endpoint.'
            ], 403);
        }

        $companyId = $actingCompany['id'];

        // Verify user is a member of this company
        $userCompanyRelation = $user->companies()
            ->where('company_id', $companyId)
            ->first();

        if (!$userCompanyRelation) {
            return response()->json([
                'code' => 'ACCESS_DENIED',
                'message' => 'You are not a member of this company.'
            ], 403);
        }

        // Get all company members
        $company = Company::findOrFail($companyId);
        $members = $company->users()->with('roles')->get();

        $membersData = $members->map(function($member) {
            $pivot = $member->pivot;
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'profile_image' => $member->profile_image,
                'role' => $member->roles->first()?->name,
                'company_role' => $pivot->role ?? null,
            ];
        });

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'members' => $membersData,
                'total' => $membersData->count()
            ]
        ]);
    }
}

