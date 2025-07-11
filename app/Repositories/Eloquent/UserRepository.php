<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $user = parent::create($data);

        if($user) {
            $user->assignRole(Role::where('id', $data['role_id'])->first()->name);
        }

        return $user;
    }

    public function update($id, array $data)
    {
        $user = parent::find($id);

        if($user) {
            $user->update($data);
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
    public function community(User $user, $get = true)
    {
        // Define role community mappings
        $roleCommunityMap = [
            'admin' => ['admin', 'attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            // 'exhibitor' => ['buyer', 'attendant', 'sponsor'],
            'exhibitor' => ['attendant', 'exhibitor', 'buyer', 'sponsor', 'speaker'],
            'buyer' => ['exhibitor', 'speaker'],
            'attendant' => ['speaker', 'exhibitor', 'attendant'],
            'speaker' => ['attendant', 'sponsor', 'exhibitor'],
            'sponsor' => ['exhibitor', 'speaker', 'attendant'],
        ];

        // Get user's primary role
        $userRole = $user->roles()->first()?->name;

        // If user has no role or unknown role, return empty collection
        if (!$userRole || !isset($roleCommunityMap[$userRole])) {
            return collect([]);
        }

        // Get the roles this user should connect with
        $communityRoles = $roleCommunityMap[$userRole];

        // Build query for users with community roles, excluding the current user
        $query = $this->model->whereHas('roles', function ($q) use ($communityRoles) {
            $q->whereIn('name', $communityRoles);
        })
        ->where('id', '!=', $user->id)
        ->where('is_active', true);

        // Return query builder or execute and return results
        if ($get) {
            return $query->get();
        }
        
        return $query;
    }
}
