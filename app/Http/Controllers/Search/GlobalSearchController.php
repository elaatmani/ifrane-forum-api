<?php

namespace App\Http\Controllers\Search;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Http\Resources\Order\OrderListResource;
use App\Http\Resources\Product\ProductListResource;

class GlobalSearchController extends Controller
{
    protected $orderRepository;
    protected $userRepository;
    protected $productRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, UserRepositoryInterface $userRepository, ProductRepositoryInterface $productRepository) {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (!$user || !($user->hasRole('admin') || $user->hasRole('agent') || $user->hasRole('followup'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $search = $request->input('search');
        $limit = 10;
        $results = [];

        // Orders search (by customer name, phone, city, id, nawris code)
        $orderCriteria = [];
        if ($search) {
            $orderCriteria['orWhere'][] = [ 'field' => 'customer_name', 'operator' => 'LIKE', 'value' => "%$search%" ];
            $orderCriteria['orWhere'][] = [ 'field' => 'customer_phone', 'operator' => 'LIKE', 'value' => "%$search%" ];
            $orderCriteria['orWhere'][] = [ 'field' => 'customer_city', 'operator' => 'LIKE', 'value' => "%$search%" ];
            $orderCriteria['orWhere'][] = [ 'field' => 'nawris_code', 'operator' => 'LIKE', 'value' => "%$search%" ];
            $orderCriteria['orWhere'][] = [ 'field' => 'id', 'operator' => 'LIKE', 'value' => "%$search%" ];
        }
        $orders = $this->orderRepository->search($orderCriteria)->take($limit);
        $results['orders'] = OrderListResource::collection($orders);

        // Products search (by name, sku, id)
        $productQuery = $this->productRepository->query();
        if ($search) {
            $productQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhere('id', 'like', "%$search%" );
            });
        }
        $products = $productQuery->orderBy('id', 'desc')->limit($limit)->get();
        
        // Transform products to add additional data
        $products = $products->map(function ($product) {
            $orderCount = $this->productRepository->getOrderCountForProduct($product->id);
            $confirmationRate = $this->productRepository->getOrderConfirmationForProduct($product->id);
            $deliveryRate = $this->productRepository->getOrderDeliveryForProduct($product->id);
            $deliveredQuantity = $this->productRepository->getDeliveredQuantityForProduct($product->id);
            $shippedQuantity = $this->productRepository->getShippedQuantityForProduct($product->id);

            $deliveredOrders = $this->productRepository->getTotalDeliveredOrdersForProduct($product->id);
            $confirmedOrders = $this->productRepository->getTotalConfirmedOrdersForProduct($product->id);

            $confirmationRate = $confirmedOrders > 0 ? ($confirmedOrders / $orderCount) * 100 : 0;
            $deliveryRate = $deliveredOrders > 0 ? ($deliveredOrders / $confirmedOrders) * 100 : 0;

            return (new ProductListResource($product))
            ->additional([
                'order_count' => $orderCount, 
                'confirmation_rate' => $confirmationRate, 
                'delivery_rate' => $deliveryRate, 
                'delivered_quantity' => $deliveredQuantity,
                'shipped_quantity' => $shippedQuantity,
            ]);
        });

        $results['products'] = $products;

        return response()->json($results);
    }
}
