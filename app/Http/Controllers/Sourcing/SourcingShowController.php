<?php

namespace App\Http\Controllers\Sourcing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sourcing\SourcingListResource;
use App\Repositories\Contracts\SourcingRepositoryInterface;
use Illuminate\Http\Request;

class SourcingShowController extends Controller
{
    protected $repository;

    public function __construct(SourcingRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $sourcing = $this->repository->find($id);
        
        if (!$sourcing) {
            return response()->json([
                'message' => 'Sourcing not found',
                'code' => 'NOT_FOUND'
            ], 404);
        }
        
        // Load the variants relationship
        $sourcing->load('variants');
        
        return response()->json([
            'data' => new SourcingListResource($sourcing),
            'code' => 'SUCCESS'
        ], 200);
    }
}
