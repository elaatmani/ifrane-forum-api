<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserDeleteController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $user = $this->repository->find($id);

        if (!$user) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'User not found'
            ], 404);
        }

        $this->repository->delete($user);

        return response()->json([
            'code' => 'SUCCESS',
        ]);
    }
}
