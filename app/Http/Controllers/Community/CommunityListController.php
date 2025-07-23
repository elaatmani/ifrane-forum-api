<?php

namespace App\Http\Controllers\Community;

use App\Http\Requests\Community\CommunityListRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Community\CommunityListResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class CommunityListController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(CommunityListRequest $request)
    {
        $per_page = $request->per_page ?? 10;
        $roles = $request->roles ?? [];

        $params = [
            'roles' => $roles,
            'search' => $request->search,
        ];

        $communityQuery = $this->userRepository->community(auth()->user(), $params, false);
        $users = $communityQuery->orderBy('id', 'desc')->paginate($per_page);
        $users->getCollection()->transform(fn ($user) => new CommunityListResource($user));

        return response()->json($users);
    }

}
