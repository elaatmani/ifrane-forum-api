<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductListResource;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductListController extends Controller
{
    protected $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $per_page = $request->per_page ?? 10;

        $products = $this->repository->query();

        $products = $products->orderBy('id', 'desc');
        
        $products = $products->paginate($per_page);

        return response()->json([
            'data' => $products,
            'code' => 'SUCCESS',
        ], 200);
    }
}
