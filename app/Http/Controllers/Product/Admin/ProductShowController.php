<?php

namespace App\Http\Controllers\Product\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\Admin\ProductShowResource;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductShowController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $product = new ProductShowResource($product);

        return response()->json([
            'data' => $product,
            'code' => 'SUCCESS',
        ], 200);
    }
}
