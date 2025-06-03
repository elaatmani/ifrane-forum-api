<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderListResource;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderResponsibilityController extends Controller
{
    protected $repository;

    public function __construct(OrderRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if(!auth()->user()->can('order.update') || !auth()->user()->hasRole('agent')) {
            return response()->json([
               'message' => 'You can\'t update orders'
            ], 403);
        }

        $notConfirmedOrder = $this->repository->query()->whereAssignedNotConfirmed(auth()->id())->get();

        if($notConfirmedOrder->count() > 0) {
            return response()->json([
               'code' => 'NOT_CONFIRMED',
               'orders' => $notConfirmedOrder->transform(fn($o) => new OrderListResource($o))
            ], 200);
        }

        $order = $this->repository->query()->whereNoAgent()->first();
        if(!$order) {
            return response()->json([
               'code' => 'NO_ORDERS',
            ], 200);
        }

        $orders = [$order];

        foreach($orders as $order) {
            $order->agent_id = auth()->id();
            $order->save();
        }

        return response()->json([
           'code' => 'SUCCESS',
           'orders' => collect($orders)->map(fn($o) => new OrderListResource($o))
        ], 200);
    }
}
