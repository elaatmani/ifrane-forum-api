<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserActiveController extends Controller
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

        $user = $this->repository->update($id, [
            'is_active' => (bool) $request->is_active
        ]);


        return response()->json([
            'code' => 'SUCCESS',
            'user' => $user
        ]);
    }
}
