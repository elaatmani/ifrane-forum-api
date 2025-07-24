<?php

namespace App\Services\Community;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CommunityMemberService
{
    public function __construct(
        private CompanyDataService $companyDataService,
        private ConnectionService $connectionService,
        private UserRecommendationService $userRecommendationService
    ) {}

    /**
     * Transform a User model into community member data format.
     *
     * @param User $user The user being viewed
     * @param User|null $authUser The authenticated user viewing the profile
     * @return array
     */
    public function transformUserToMemberData(User $user, ?User $authUser): array
    {
        try {
            $companies = $user->companies;
            $company = $companies->first();
            $profile = $user->profile;
            $role = $user->roles->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'role' => $role?->name,
                'badge' => $role ? ucfirst($role->name) : '',
                'company' => $this->companyDataService->formatCompanyData($company),
                'connection' => $this->connectionService->getConnectionData($user, $authUser),
                'profile' => $this->formatProfileData($profile),
                'recommendations' => $this->userRecommendationService->getRecommendationsForUser($user, $authUser),
                'is_bookmarked' => $user->isBookmarked()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to transform user to member data', [
                'user_id' => $user->id,
                'auth_user_id' => $authUser?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return basic user data on error to prevent complete failure
            return $this->getBasicUserData($user);
        }
    }

    /**
     * Format user profile data.
     *
     * @param \App\Models\UserProfile|null $profile
     * @return array
     */
    private function formatProfileData($profile): array
    {
        if (!$profile) {
            return [
                'about' => null,
                'linkedin_url' => null,
                'instagram_url' => null,
                'twitter_url' => null,
                'facebook_url' => null,
                'youtube_url' => null,
                'github_url' => null,
                'website_url' => null,
                'contact_email' => null,
                'language' => null,
                'street' => null,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
            ];
        }

        return [
            'about' => $profile->about,
            'linkedin_url' => $profile->linkedin_url,
            'instagram_url' => $profile->instagram_url,
            'twitter_url' => $profile->twitter_url,
            'facebook_url' => $profile->facebook_url,
            'youtube_url' => $profile->youtube_url,
            'github_url' => $profile->github_url,
            'website_url' => $profile->website_url,
            'contact_email' => $profile->contact_email,
            'language' => $profile->language,
            'street' => $profile->street,
            'city' => $profile->city,
            'state' => $profile->state,
            'postal_code' => $profile->postal_code,
            'country' => $profile->country_name,
        ];
    }

    /**
     * Get basic user data as fallback when full transformation fails.
     *
     * @param User $user
     * @return array
     */
    private function getBasicUserData(User $user): array
    {
        $role = $user->roles->first();
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->profile_image,
            'role' => $role?->name,
            'badge' => $role ? ucfirst($role->name) : '',
            'company' => null,
            'connection' => [
                'id' => null,
                'status' => null,
                'is_sent_by_me' => false,
                'can_connect' => false,
                'mutual_connections' => [],
                'mutual_connections_count' => 0,
            ],
            'profile' => $this->formatProfileData(null),
            'recommendations' => [],
        ];
    }
} 