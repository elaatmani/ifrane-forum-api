<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\User\UserListResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserStoreController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreUserRequest $request)
    {
        $data = $request->validated();

        if($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users', 'public');
        }

        $user = $this->repository->create($data);

        if ($user) {
            $user->is_active = true;

            return response()->json([
                'code' => 'SUCCESS',
                'user' => new UserListResource($user)
            ], 201);
        }

        return response()->json([
            'code' => 'ERROR',
            'message' => 'User creation failed'
        ], 500);
    }
}
