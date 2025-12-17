<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MyCompanyMeetingsController extends Controller
{
    /**
     * Get all meetings related to company users.
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

        // Get all company users
        $company = Company::findOrFail($companyId);
        $companyUserIds = $company->users()->pluck('users.id');

        // Get meetings where:
        // 1. Organizer is a company user
        // 2. Any participant is a company user
        // 3. Company is directly involved (for member_to_company meetings)
        $query = Meeting::where(function($q) use ($companyUserIds, $companyId) {
            $q->whereIn('organizer_id', $companyUserIds)
              ->orWhereIn('user_id', $companyUserIds)
              ->orWhere('company_id', $companyId)
              ->orWhereHas('participants', function($p) use ($companyUserIds) {
                  $p->whereIn('user_id', $companyUserIds);
              });
        })
        ->with(['organizer', 'user', 'company', 'participants.user']);

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('meeting_type', $request->type);
        }

        if ($request->has('upcoming')) {
            $query->upcoming();
        }

        $meetings = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $meetings
        ]);
    }
}

