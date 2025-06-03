<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserOrderResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserByRoleController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request)
    {
        switch ($request->input('all')) {
            case true:
                return $this->index($request);
            default:
                return $this->paginate($request);
        }
    }

    public function index(Request $request)
    {
        $roles = $request->query('roles', []);
        $users = $this->repository->query()->whereHas("roles", function ($q) use($roles) {
            $q->whereIn("name", $roles);
        })->get();
        $users->transform(fn ($user) => new UserOrderResource($user));

        return response()->json([
            'users' => $users,
            'code' => 'SUCCESS',
        ], 200);
    }

    public function paginate(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $roles = $request->query('roles', []);
        $users = $this->repository->query()->whereHas("roles", function ($q) use($roles) {
            $q->whereIn("name", $roles);
        })->orderBy('id', 'desc')->paginate($per_page);
        $users->getCollection()->transform(fn ($user) => new UserListResource($user));

        return response()->json([
            'data' => $users,
            'code' => 'SUCCESS',
        ], 200);
    }
}
