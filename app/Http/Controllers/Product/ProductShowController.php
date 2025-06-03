<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductShowController extends Controller
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

    public function agents(Request $request, $id)
    {
        $agents = $this->repository->getAgentsForProduct($id);

        return response()->json([
            'agents' => $agents,
            'code' => 'SUCCESS'
        ]);
    }

    public function agents_by_range(Request $request, $id)
    {
        $agents = $this->repository->getAgentsForProductByRange($id, $request->all());

        return response()->json([
            'agents' => $agents,
            'code' => 'SUCCESS'
        ]);
    }

    public function marketers(Request $request, $id)
    {
        $marketers = $this->repository->getMarketersForProduct($id);

        return response()->json([
            'marketers' => $marketers,
            'code' => 'SUCCESS'
        ]);
    }

    public function marketers_by_range(Request $request, $id)
    {
        $marketers = $this->repository->getMarketersForProductByRange($id, $request->all());

        return response()->json([
            'marketers' => $marketers,
            'code' => 'SUCCESS'
        ]);
    }


    public function product_status(Request $request, $id)
    {
        $results = $this->repository->getStatusForProduct($id);
        $ad_spend = $this->repository->getAdSpendForProduct($id);

        return response()->json([
            'data' => $results,
            'ad_spend' => $ad_spend,
            'code' => 'SUCCESS'
        ]);
    }


    public function inventory(Request $request, $id)
    {
        $product = $this->repository->find($id);

        $quantity = $product->quantity;

        if($product->has_variants) {
            $quantity = $product->variants->sum('quantity');
        }

        $deliveredQuantity = $this->repository->getDeliveredQuantityForProduct($id);
        $shippedQuantity = $this->repository->getShippedQuantityForProduct($id);


        return response()->json([
            'data' => [
                'delivered_quantity' => $deliveredQuantity,
                'shipped_quantity' => $shippedQuantity,
                'available_quantity' => $quantity - $deliveredQuantity - $shippedQuantity,
                'total_quantity' => $quantity,
            ],
            'code' => 'SUCCESS'
        ]);
    }
}
