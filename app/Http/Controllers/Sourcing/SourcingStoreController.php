<?php

namespace App\Http\Controllers\Sourcing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sourcing\StoreSourcingRequest;
use App\Repositories\Contracts\SourcingRepositoryInterface;

class SourcingStoreController extends Controller
{
    protected $repository;

    public function __construct(SourcingRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->all();
        // return response()->json($data);
        $sourcing = $this->repository->create($data);

        return response()->json([
            'code' => 'SUCCESS',
            'sourcing' => $sourcing,
            'message' => 'Sourcing created successfully',
        ], 201);
    }
}
