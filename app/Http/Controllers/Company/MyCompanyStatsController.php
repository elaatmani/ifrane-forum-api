<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Meeting;
use App\Models\UserConnection;
use Illuminate\Http\Request;

class MyCompanyStatsController extends Controller
{
    /**
     * Get company statistics.
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

        // Get company with relationships
        $company = Company::withCount([
            'products',
            'services',
            'users',
            'categories',
            'certificates',
            'meetings',
        ])->findOrFail($companyId);

        // Get company user IDs for contacts calculation
        $companyUserIds = $company->users()->pluck('users.id');

        // Count contacts (unique connected users)
        $connections = UserConnection::where('status', UserConnection::STATUS_ACCEPTED)
            ->where(function($query) use ($companyUserIds) {
                $query->whereIn('sender_id', $companyUserIds)
                      ->orWhereIn('receiver_id', $companyUserIds);
            })
            ->get();

        $connectedUserIds = collect();
        foreach ($connections as $connection) {
            $senderIsCompanyUser = $companyUserIds->contains($connection->sender_id);
            $receiverIsCompanyUser = $companyUserIds->contains($connection->receiver_id);

            if ($senderIsCompanyUser && !$receiverIsCompanyUser) {
                $connectedUserIds->push($connection->receiver_id);
            } elseif ($receiverIsCompanyUser && !$senderIsCompanyUser) {
                $connectedUserIds->push($connection->sender_id);
            }
        }
        $totalContacts = $connectedUserIds->unique()->count();

        // Meeting statistics - get all meetings related to company users
        $allMeetingsQuery = Meeting::where(function($query) use ($companyUserIds, $companyId) {
            $query->whereIn('organizer_id', $companyUserIds)
                  ->orWhereIn('user_id', $companyUserIds)
                  ->orWhere('company_id', $companyId)
                  ->orWhereHas('participants', function($p) use ($companyUserIds) {
                      $p->whereIn('user_id', $companyUserIds);
                  });
        });

        $totalMeetings = $allMeetingsQuery->count();
        $upcomingMeetings = (clone $allMeetingsQuery)
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'accepted'])
            ->count();
        $pendingMeetings = (clone $allMeetingsQuery)
            ->where('status', 'pending')
            ->count();
        $acceptedMeetings = (clone $allMeetingsQuery)
            ->where('status', 'accepted')
            ->count();
        $completedMeetings = (clone $allMeetingsQuery)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'products' => [
                    'total' => $company->products_count,
                ],
                'services' => [
                    'total' => $company->services_count,
                ],
                'members' => [
                    'total' => $company->users_count,
                ],
                'contacts' => [
                    'total' => $totalContacts,
                ],
                'meetings' => [
                    'total' => $totalMeetings,
                    'upcoming' => $upcomingMeetings,
                    'pending' => $pendingMeetings,
                    'accepted' => $acceptedMeetings,
                    'completed' => $completedMeetings,
                ],
                'categories' => [
                    'total' => $company->categories_count,
                ],
                'certificates' => [
                    'total' => $company->certificates_count,
                ],
            ]
        ]);
    }
}

