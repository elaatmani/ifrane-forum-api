<?php

namespace App\Services\Community;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UserRecommendationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CompanyDataService $companyDataService
    ) {}

    /**
     * Get user recommendations for the "You may also like" section.
     *
     * @param User $targetUser The user being viewed
     * @param User|null $authUser The authenticated user viewing the profile
     * @return array
     */
    public function getRecommendationsForUser(User $targetUser, ?User $authUser): array
    {
        if (!$authUser) {
            return [];
        }

        try {
            $similarUsers = $this->getSimilarUsers($authUser);
            
            if ($similarUsers->isEmpty()) {
                return [];
            }

            $existingConnectionIds = $this->getExistingConnectionIds($authUser);
            
            return $this->transformSimilarUsersToRecommendations(
                $similarUsers,
                $existingConnectionIds,
                $targetUser->id
            );

        } catch (\Exception $e) {
            Log::warning('Failed to get user recommendations', [
                'target_user_id' => $targetUser->id,
                'auth_user_id' => $authUser->id,
                'error' => $e->getMessage()
            ]);
            
            // Gracefully handle any errors - don't break the main resource
            return [];
        }
    }

    /**
     * Get similar users using the repository method.
     *
     * @param User $authUser
     * @return \Illuminate\Support\Collection
     */
    private function getSimilarUsers(User $authUser)
    {
        return $this->userRepository->getSimilarUsers($authUser, [
            'limit' => 8,
            'factors' => [
                'role_compatibility' => ['enabled' => true, 'weight' => 3],
                'geographic_proximity' => ['enabled' => true, 'weight' => 2],
                'industry_alignment' => ['enabled' => true, 'weight' => 2],
                'network_connections' => ['enabled' => true, 'weight' => 3]
            ]
        ]);
    }

    /**
     * Get existing connection IDs to exclude from recommendations.
     *
     * @param User $authUser
     * @return \Illuminate\Support\Collection
     */
    private function getExistingConnectionIds(User $authUser)
    {
        return $authUser->allConnections()
            ->pluck('sender_id')
            ->merge($authUser->allConnections()->pluck('receiver_id'))
            ->unique()
            ->filter(fn($id) => $id !== $authUser->id);
    }

    /**
     * Transform similar users into recommendation format.
     *
     * @param \Illuminate\Support\Collection $similarUsers
     * @param \Illuminate\Support\Collection $existingConnectionIds
     * @param int $targetUserId The user being viewed (to exclude)
     * @return array
     */
    private function transformSimilarUsersToRecommendations($similarUsers, $existingConnectionIds, int $targetUserId): array
    {
        return $similarUsers
            ->reject(fn($user) => $existingConnectionIds->contains($user->id))
            ->reject(fn($user) => $user->id === $targetUserId) // Exclude the user being viewed
            ->map(function($user) {
                return $this->formatRecommendationUser($user);
            })
            ->values()
            ->toArray();
    }

    /**
     * Format a single user for recommendation output.
     *
     * @param User $user
     * @return array
     */
    private function formatRecommendationUser(User $user): array
    {
        $userCompany = $user->companies->first();
        $userRole = $user->roles->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'profile_image' => $user->profile_image,
            'role' => $userRole?->name,
            'badge' => $userRole ? ucfirst($userRole->name) : '',
            'company' => $this->companyDataService->formatRecommendationCompanyData($userCompany),
            'similarity_score' => $user->similarity_score ?? 0,
            'location' => $user->profile?->country_name,
        ];
    }

    /**
     * Get configuration for recommendations display.
     *
     * @return array
     */
    public function getRecommendationConfig(): array
    {
        return [
            'title' => 'You may also like',
            'description' => 'Here\'s an opportunity to contact and meet other potential companies or partners.',
            'max_items' => 8,
        ];
    }
} 