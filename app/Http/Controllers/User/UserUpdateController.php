<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserListResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserUpdateController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateUserRequest $request, $id)
    {
        $user = $this->repository->update($id, $request->validated());

        return response()->json([
            'code' => 'SUCCESS',
            'user' => new UserListResource($user)
        ]);
    }
}
