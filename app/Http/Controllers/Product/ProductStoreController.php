<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductStoreController extends Controller
{

    protected $repository;

    public function __construct(ProductRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {


        return response()->json([
            'message' => 'Product created successfully'
        ], 201);
    }
}
