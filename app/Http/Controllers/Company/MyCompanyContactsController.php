<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Http\Request;

class MyCompanyContactsController extends Controller
{
    /**
     * Get all contacts (connected users) for the company.
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

        // Get all accepted connections from company users
        $connections = UserConnection::where('status', UserConnection::STATUS_ACCEPTED)
            ->where(function($query) use ($companyUserIds) {
                $query->whereIn('sender_id', $companyUserIds)
                      ->orWhereIn('receiver_id', $companyUserIds);
            })
            ->with(['sender.roles', 'receiver.roles', 'sender.companies', 'receiver.companies'])
            ->get();

        // Collect unique connected users with their connections to company users
        $contactsMap = [];

        foreach ($connections as $connection) {
            // Determine which user is the company user and which is the connected user
            $senderIsCompanyUser = $companyUserIds->contains($connection->sender_id);
            $receiverIsCompanyUser = $companyUserIds->contains($connection->receiver_id);

            // Get the connected user (the one who is NOT a company member) and company user
            if ($senderIsCompanyUser && !$receiverIsCompanyUser) {
                $connectedUser = $connection->receiver;
                $companyUser = $connection->sender;
            } elseif ($receiverIsCompanyUser && !$senderIsCompanyUser) {
                $connectedUser = $connection->sender;
                $companyUser = $connection->receiver;
            } else {
                // Both are company users or neither is, skip
                continue;
            }

            // Initialize contact entry if not exists
            if (!isset($contactsMap[$connectedUser->id])) {
                // Get user roles and prioritize non-admin roles
                $userRoles = $connectedUser->roles;
                $role = null;
                
                if ($userRoles->isNotEmpty()) {
                    // Try to find a non-admin role first
                    $role = $userRoles->first(function($role) {
                        return $role->name !== 'admin';
                    });
                    
                    // If no non-admin role found, use the first role (which might be admin)
                    if (!$role) {
                        $role = $userRoles->first();
                    }
                }

                // Get user's first company (if exists)
                $userCompany = $connectedUser->companies->first();
                $companyName = $userCompany?->name;
                $companyLogo = $userCompany?->logo;

                $contactsMap[$connectedUser->id] = [
                    'id' => $connectedUser->id,
                    'name' => $connectedUser->name,
                    'email' => $connectedUser->email,
                    'profile_image' => $connectedUser->profile_image,
                    'role' => $role?->name,
                    'company_name' => $companyName,
                    'company_logo' => $companyLogo,
                    'connections' => [],
                ];
            }

            // Add connection info (which company user and when)
            $contactsMap[$connectedUser->id]['connections'][] = [
                'company_user' => [
                    'id' => $companyUser->id,
                    'name' => $companyUser->name,
                    'email' => $companyUser->email,
                    'profile_image' => $companyUser->profile_image,
                ],
                'connected_at' => $connection->responded_at ?? $connection->created_at,
            ];
        }

        // Convert map to array and sort connections by date
        $connectedUsers = collect($contactsMap)->map(function($contact) {
            // Sort connections by date (most recent first)
            usort($contact['connections'], function($a, $b) {
                return $b['connected_at'] <=> $a['connected_at'];
            });
            return $contact;
        })->values();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'contacts' => $connectedUsers->values(),
                'total' => $connectedUsers->count()
            ]
        ]);
    }
}

