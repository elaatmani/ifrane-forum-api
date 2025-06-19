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
        $data = $request->validated();

        if($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users', 'public');
        }

        $user = $this->repository->update($id, $data);

        return response()->json([
            'code' => 'SUCCESS',
            'user' => new UserListResource($user)
        ]);
    }
}
