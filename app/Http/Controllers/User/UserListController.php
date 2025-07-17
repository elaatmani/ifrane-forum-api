<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserOrderResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserListController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        switch ($request->input('all')) {
            case true:
                return $this->index($request);
            default:
                return $this->paginate($request);
        }
    }

    public function index(Request $request) {
        $users = $this->repository->all();
        $users->transform(fn($user) => new UserOrderResource($user));

        return response()->json([
            'users' => $users,
            'code' => 'SUCCESS',
        ], 200);
    }

    public function paginate(Request $request) {
        $per_page = $request->per_page ?? 10;
        $users = $this->repository->query();
        
        
        $users->when($request->has('search'), function ($query) use ($request) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        });

        // companies search
        $users->when($request->has('companies'), function ($query) use ($request) {
            $query->whereHas('companies', function ($subQuery) use ($request) {
                $subQuery->where('companies.id', $request->companies);
            });
        });

        // roles by id
        $users->when($request->has('roles'), function ($query) use ($request) {
            $query->whereHas('roles', function ($subQuery) use ($request) {
                $subQuery->where('roles.id', $request->roles);
            });
        });

        // by status
        $users->when($request->has('status'), function ($query) use ($request) {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        });

        
        
        $users = $users->orderBy('id', 'desc')->paginate($per_page);
        $users->getCollection()->transform(fn($user) => new UserListResource($user));

        return response()->json([
            'data' => $users,
            'code' => 'SUCCESS',
        ], 200);
    }


}
