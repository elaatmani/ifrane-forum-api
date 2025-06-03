<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductEditController extends Controller
{

    protected $repository;

    public function __construct(ProductRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $product = $this->repository->find($id);

        if(!$product) {
            return response()->json([
                'code' => 'NOT_FOUND'
            ], 404);
        }

        $product->load('variants');
        $product->load('product_crosses');
        $product->load('offers');

        $product->cross_products = $product->product_crosses;

        return response()->json([
            'product' => $product,
            'code' => 'SUCCESS'
        ]);
    }
}
