<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\UserRepositoryInterface;

class ActAsRoleController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $role)
    {
        $user = auth()->user();

        if(!$user->hasRole($role)) {
            return response()->json([
                'message' => 'You are not authorized to act as this role'
            ], 400);
        }

        $this->repository->actAsRole($role);

        return response()->json([
            'message' => 'You are now acting as ' . $role
        ], 200);
    }
}
