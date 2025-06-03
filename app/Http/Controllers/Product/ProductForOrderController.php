<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductForOrderCollection;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductForOrderController extends Controller
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
        $products = $this->repository->query()->orderBy('id', 'desc')->get()
        ->transform(fn($product) => new ProductForOrderCollection($product));

        return response()->json([
            'products' => $products,
            'code' => 'SUCCESS',
        ], 200);
    }

    public function offers(Request $request)
    {
        $ids = $request->ids;
        $offers = $this->repository->getOffersForProduct($ids);

        return response()->json([
            'offers' => $offers,
            'code' => 'SUCCESS',
        ], 200);
    }

    public function cross_products(Request $request)
    {
        $ids = $request->ids;
        $crossProducts = $this->repository->getCrossProductsForProduct($ids);

        return response()->json([
            'cross_products' => $crossProducts,
            'code' => 'SUCCESS',
        ], 200);
    }
}
