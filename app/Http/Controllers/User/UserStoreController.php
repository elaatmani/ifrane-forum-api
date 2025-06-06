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

        $user = $this->repository->create($request->validated());

        $user->is_active = true;

        return response()->json([
            'code' => 'SUCCESS',
            'user' =>  new UserListResource($user)
        ], 201);
    }
}
