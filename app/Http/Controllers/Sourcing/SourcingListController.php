<?php

namespace App\Http\Controllers\Sourcing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Sourcing\SourcingListResource;
use App\Repositories\Contracts\SourcingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SourcingListController extends Controller
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
        $per_page = $request->per_page ?? 10;
        $search = $request->search;

        $query = $this->repository->query();
        
        // Eager load the variants relationship
        $query->with('variants');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_url', 'like', "%{$search}%")
                  ->orWhere('destination_country', 'like', "%{$search}%")
                  ->orWhere('shipping_method', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $sourcings = $query->orderBy('id', 'desc')->paginate($per_page);
        $sourcings->getCollection()->transform(fn($sourcing) => new SourcingListResource($sourcing));

        return response()->json([
            'data' => $sourcings,
            'code' => 'SUCCESS',
        ], 200);
    }
}
