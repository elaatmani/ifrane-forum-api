<?php

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Community\CommunityMemberRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Resources\Community\CommunityMemberResource;

class CommunityMemberController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(CommunityMemberRequest $request, $id)
    {
        $user = $this->userRepository->find($id);

        if(!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(new CommunityMemberResource($user));
    }
}
