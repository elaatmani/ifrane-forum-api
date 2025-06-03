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
        
        if ($request->has('search') && !empty($request->search)) {
            $products->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
                    ->orWhere('id', 'like', '%' . $request->search . '%');
            });
        }

        $products = $products->paginate($per_page);

        $products->getCollection()->transform(function ($product) {
            $orderCount = $this->repository->getOrderCountForProduct($product->id);
            $deliveredQuantity = $this->repository->getDeliveredQuantityForProduct($product->id);
            $shippedQuantity = $this->repository->getShippedQuantityForProduct($product->id);

            $deliveredOrders = $this->repository->getTotalDeliveredOrdersForProduct($product->id);
            $confirmedOrders = $this->repository->getTotalConfirmedOrdersForProduct($product->id);
            $duplicatedOrders = $this->repository->getTotalDuplicatedOrdersForProduct($product->id);

            $confirmationRate = $confirmedOrders > 0 ? round(($confirmedOrders / ($orderCount - $duplicatedOrders)) * 100, 2) : 0;
            $deliveryRate = $deliveredOrders > 0 ? round(($deliveredOrders / $confirmedOrders) * 100, 2) : 0;

            return (new ProductListResource($product))
            ->additional([
                'order_count' => $orderCount, 
                'confirmation_rate' => $confirmationRate, 
                'delivery_rate' => $deliveryRate, 
                'delivered_quantity' => $deliveredQuantity,
                'shipped_quantity' => $shippedQuantity,
            ]);
        });

        return response()->json([
            'data' => $products,
            'code' => 'SUCCESS',
        ], 200);
    }
}
