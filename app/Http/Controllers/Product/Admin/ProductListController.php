<?php

namespace App\Http\Controllers\Product\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\Admin\ProductListResource;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductListController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $per_page = $request->per_page ?? 10;

        $products = $this->productRepository->query();

        if ($request->has('search')) {
            $products = $products->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $products->orderBy('id', 'desc');
        
        $products = $products->paginate($per_page);

        $products->getCollection()->transform(function ($product) {
            return new ProductListResource($product);
        });

        return response()->json([
            'data' => $products,
            'code' => 'SUCCESS',
        ], 200);
    }
}
