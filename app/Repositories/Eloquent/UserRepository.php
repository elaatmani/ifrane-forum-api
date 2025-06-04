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
}
