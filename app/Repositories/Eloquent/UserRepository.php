<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $user = parent::create($data);

        if($user && isset($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $role = Role::where('id', $roleId)->first();
                if ($role) {
                    $user->assignRole($role->name);
                }
            }
        }

        return $user;
    }

    public function update($id, array $data)
    {
        $user = parent::find($id);

        if($user) {
            $user->update($data);
        }

        if($user && isset($data['roles'])) {
            // Remove all current roles
            $user->roles()->detach();

            // Assign only the roles specified in the data
            foreach ($data['roles'] as $roleId) {
                $role = Role::where('id', $roleId)->first();
                if ($role) {
                    $user->assignRole($role->name);
                }
            }
        }

        return $user;
    }


    public function findByRole($role, $get = true)
    {
        if($get) {
            return Role::findByName($role, 'web')?->users()->get() ?? collect([]);
        }
        return Role::findByName($role, 'web')?->users() ?? collect([]);
    }

    /**
     * Get community users based on role relationships
     * 
     * Role Mappings:
     * - Admin: All users (admin, attendant, exhibitor, buyer, sponsor, speaker)
     * - Exhibitor: buyer, attendant, sponsor (potential customers and supporters)
     * - Buyer: exhibitor, speaker (sources of products and knowledge)
     * - Attendant: speaker, exhibitor, attendant (learning and networking)
     * - Speaker: attendant, sponsor, exhibitor (audience and collaborators)
     * - Sponsor: exhibitor, speaker, attendant (those they support)
     * 
     * @param User $user The user to find community for
     * @param bool $get Whether to execute the query and return results (true) or return query builder (false)
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder Collection of User models or Query Builder
     * 
     * Example usage:
     * // Get results directly
     * $community = $userRepository->community($user);
     * 
     * // Get query builder for pagination
     * $communityQuery = $userRepository->community($user, false);
     * $paginatedCommunity = $communityQuery->paginate(10);
     * 
     * To test via tinker:
     * php artisan tinker
     * $repo = app(\App\Repositories\Contracts\UserRepositoryInterface::class);
     * $user = \App\Models\User::first();
     * $community = $repo->community($user);
     * echo "User role: " . $user->roles->first()->name;
     * echo "Community size: " . $community->count();
     * $community->each(fn($u) => echo $u->name . " (" . $u->roles->first()->name . ")");
     */
    public function community(User $user, $params = [], $get = true)
    {
        // Define role community mappings
        // $roleCommunityMap = [
        //     'admin' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
        //     // 'exhibitor' => ['buyer', 'attendant', 'sponsor'],
        //     'exhibitor' => ['attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
        //     'buyer' => ['exhibitor', 'speaker'],
        //     'attendant' => ['speaker', 'exhibitor', 'attendant'],
        //     'speaker' => ['attendant', 'sponsor', 'exhibitor'],
        //     'sponsor' => ['exhibitor', 'speaker', 'attendant'],
        // ];

        // for testing we allowed all
        $roleCommunityMap = [
            'admin' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            // 'exhibitor' => ['buyer', 'attendant', 'sponsor'],
            'exhibitor' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            'buyer' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            'attendant' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            'speaker' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            'sponsor' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
        ];

        // Get user's primary role
        $userRole = $user->roles()->first()?->name;

        $roles = data_get($params, 'roles', []);
        $search = data_get($params, 'search', '');

        // If user has no role or unknown role, return empty collection
        if (!$userRole || !isset($roleCommunityMap[$userRole])) {
            return collect([]);
        }

        // Get the roles this user should connect with
        $communityRoles = $roleCommunityMap[$userRole];

        if($roles) {
            $communityRoles = array_intersect($communityRoles, $roles);
        }

        // Build query for users with community roles, excluding the current user
        $query = $this->model->whereHas('roles', function ($q) use ($communityRoles) {
            $q->whereIn('name', $communityRoles);
        })
        ->where('id', '!=', $user->id)
        ->where('is_active', true);

        // Only show completed profiles that passed onboarding
        // $query->where('is_completed', true);

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Return query builder or execute and return results
        if ($get) {
            return $query->get();
        }
        
        return $query;
    }

    /**
     * Get similar users for a user based on configurable recommendation factors
     * 
     * This method implements a scoring system with 4 configurable factors:
     * 1. Role Compatibility - Users in compatible business roles
     * 2. Geographic Proximity - Users in same country/region  
     * 3. Industry Alignment - Users in similar business industries (via companies)
     * 4. Network Connections - Users with mutual connections
     * 
     * @param User $user The user to get recommendations for
     * @param array $params Configuration parameters including factors, limits, etc.
     * @param bool $get Whether to execute the query (true) or return collection (false)
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     * 
     * Example usage:
     * // Use default factors
     * $similarUsers = $userRepository->getSimilarUsers($user);
     * 
     * // Custom factor configuration
     * $similarUsers = $userRepository->getSimilarUsers($user, [
     *     'limit' => 15,
     *     'factors' => [
     *         'role_compatibility' => ['enabled' => true, 'weight' => 5],
     *         'geographic_proximity' => ['enabled' => false, 'weight' => 1],
     *         'industry_alignment' => ['enabled' => true, 'weight' => 3],
     *         'network_connections' => ['enabled' => true, 'weight' => 4]
     *     ]
     * ]);
     */
    public function getSimilarUsers(User $user, array $params = [], bool $get = true)
    {
        // Get configuration from config file and merge with params
        $defaultConfig = Config::get('recommendations.users', []);
        $defaultFactors = $defaultConfig['default_factors'] ?? [];
        $factorParams = $params['factors'] ?? [];
        
        // Merge default factors with provided factor overrides
        $factors = [];
        foreach ($defaultFactors as $factorName => $defaultSettings) {
            $factors[$factorName] = array_merge(
                $defaultSettings, 
                $factorParams[$factorName] ?? []
            );
        }

        // Get limits and filtering settings
        $limit = $params['limit'] ?? $defaultConfig['default_limit'] ?? 10;
        $minScore = $params['min_score'] ?? $defaultConfig['min_score_threshold'] ?? 3;
        $maxLimit = $defaultConfig['max_limit'] ?? 50;
        $filtering = $defaultConfig['filtering'] ?? [];
        
        // Ensure limit doesn't exceed maximum
        $limit = min($limit, $maxLimit);

        // Get base query for potential candidates
        $candidatesQuery = $this->model->with(['roles', 'profile.country', 'companies.categories'])
            ->where('id', '!=', $user->id);

        // Apply filtering options
        if ($filtering['require_active_users'] ?? true) {
            $candidatesQuery->where('is_active', true);
        }
        
        if ($filtering['require_completed_profiles'] ?? true) {
            $candidatesQuery->where('is_completed', true);
        }

        // Exclude existing connections if configured
        if ($filtering['exclude_existing_connections'] ?? false) {
            $existingConnectionIds = $user->acceptedConnections()
                ->pluck('sender_id')
                ->merge($user->acceptedConnections()->pluck('receiver_id'))
                ->unique()
                ->filter(fn($id) => $id !== $user->id);
            
            if ($existingConnectionIds->isNotEmpty()) {
                $candidatesQuery->whereNotIn('id', $existingConnectionIds);
            }
        }

        $candidates = $candidatesQuery->get();

        // If no candidates found, return empty collection
        if ($candidates->isEmpty()) {
            return $get ? collect([]) : collect([]);
        }

        // Calculate scores for each candidate
        $scoredCandidates = $candidates->map(function ($candidate) use ($user, $factors) {
            $score = $this->calculateUserSimilarityScore($user, $candidate, $factors);
            $candidate->similarity_score = $score;
            return $candidate;
        });

        // Filter by minimum score and sort by score descending
        $recommendedUsers = $scoredCandidates
            ->filter(fn($candidate) => $candidate->similarity_score >= $minScore)
            ->sortByDesc('similarity_score')
            ->take($limit)
            ->values();

        return $get ? $recommendedUsers : $recommendedUsers;
    }

    /**
     * Calculate similarity score between two users based on enabled factors
     * 
     * @param User $user The requesting user
     * @param User $candidate The candidate user to score
     * @param array $factors Factor configuration with enabled/weight settings
     * @return int Total similarity score
     */
    private function calculateUserSimilarityScore(User $user, User $candidate, array $factors): int
    {
        $totalScore = 0;
        $config = Config::get('recommendations.users.scoring', []);

        // Factor 1: Role Compatibility
        if ($factors['role_compatibility']['enabled'] ?? false) {
            $roleScore = $this->calculateRoleCompatibilityScore($user, $candidate, $config);
            $weight = $factors['role_compatibility']['weight'] ?? 1;
            $totalScore += $roleScore * $weight;
        }

        // Factor 2: Geographic Proximity  
        if ($factors['geographic_proximity']['enabled'] ?? false) {
            $geoScore = $this->calculateGeographicProximityScore($user, $candidate, $config);
            $weight = $factors['geographic_proximity']['weight'] ?? 1;
            $totalScore += $geoScore * $weight;
        }

        // Factor 3: Industry Alignment
        if ($factors['industry_alignment']['enabled'] ?? false) {
            $industryScore = $this->calculateIndustryAlignmentScore($user, $candidate, $config);
            $weight = $factors['industry_alignment']['weight'] ?? 1;
            $totalScore += $industryScore * $weight;
        }

        // Factor 4: Network Connections
        if ($factors['network_connections']['enabled'] ?? false) {
            $networkScore = $this->calculateNetworkConnectionsScore($user, $candidate, $config);
            $weight = $factors['network_connections']['weight'] ?? 1;
            $totalScore += $networkScore * $weight;
        }

        return $totalScore;
    }

    /**
     * Calculate role compatibility score between users
     */
    private function calculateRoleCompatibilityScore(User $user, User $candidate, array $config): int
    {
        $userRole = $user->roles()->first()?->name;
        $candidateRole = $candidate->roles()->first()?->name;
        
        if (!$userRole || !$candidateRole) {
            return 0;
        }

        // Get role targeting configuration
        $roleTargeting = Config::get('recommendations.users.role_targeting', []);
        $compatibleRoles = $roleTargeting[$userRole] ?? [];
        
        // Check if candidate's role is compatible
        if (in_array($candidateRole, $compatibleRoles)) {
            return $config['role_compatibility_points'] ?? 3;
        }

        return 0;
    }

    /**
     * Calculate geographic proximity score between users
     */
    private function calculateGeographicProximityScore(User $user, User $candidate, array $config): int
    {
        $userCountryId = $user->profile?->country_id;
        $candidateCountryId = $candidate->profile?->country_id;
        
        if (!$userCountryId || !$candidateCountryId) {
            return 0;
        }

        if ($userCountryId === $candidateCountryId) {
            $baseScore = $config['geographic_match_points'] ?? 2;
            $bonus = $config['same_country_bonus'] ?? 1;
            return $baseScore + $bonus;
        }

        return 0;
    }

    /**
     * Calculate industry alignment score based on company categories
     */
    private function calculateIndustryAlignmentScore(User $user, User $candidate, array $config): int
    {
        $userCategoryIds = $user->companies->pluck('categories')->flatten()->pluck('id')->unique();
        $candidateCategoryIds = $candidate->companies->pluck('categories')->flatten()->pluck('id')->unique();
        
        if ($userCategoryIds->isEmpty() || $candidateCategoryIds->isEmpty()) {
            return 0;
        }

        // Count matching industry categories
        $commonCategories = $userCategoryIds->intersect($candidateCategoryIds);
        $matchCount = $commonCategories->count();
        
        if ($matchCount > 0) {
            $baseScore = $config['industry_match_points'] ?? 2;
            return $baseScore * $matchCount;
        }

        return 0;
    }

    /**
     * Calculate network connections score based on mutual connections
     */
    private function calculateNetworkConnectionsScore(User $user, User $candidate, array $config): int
    {
        // Get mutual connections between users
        $mutualConnections = $user->getMutualConnectionsWith($candidate->id);
        $mutualCount = $mutualConnections->count();
        
        if ($mutualCount > 0) {
            $baseScore = $config['mutual_connection_points'] ?? 3;
            $bonus = $config['multiple_connection_bonus'] ?? 1;
            
            // Award base score plus bonus for multiple mutual connections
            return $baseScore + ($mutualCount > 1 ? $bonus : 0);
        }

        return 0;
    }


    public function actAsRole($role)
    {

        $user = auth()->user();

        if (is_numeric($role)) {
            $role = Role::find($role);

            if(!$role) {
                return false;
            }

            $role = $role->name;
        }

        if(!$user) {
            return false;
        }

        if(!$user->hasRole($role)) {
            return false;
        }

        $user->update([
            'acting_as_role' => $role
        ]);

        return true;
    }
}
