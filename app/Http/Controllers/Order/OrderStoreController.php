<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\Order\OrderListResource;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderStoreController extends Controller
{
    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreOrderRequest $request)
    {
        
        try {

            $data = $request->all();
            
            if(auth()->user()->hasRole('agent') || auth()->user()->hasRole('admin')) {
                $data['agent_id'] = auth()->id();
            }
            
            if(auth()->user()->hasRole('followup')) {
                $data['followup_assigned_at'] = now();
                $data['followup_id'] = auth()->id();
            }

            if(data_get($data, 'followup_status', null) == 'reconfirmed') {
                $data['reconfirmed_at'] = now();
            }

            $data['created_by'] = auth()->id();

            // Update the order with the filtered fields
            $order = $this->repository->create($data, $request->items);
            return response()->json([
                'order' => new OrderListResource($order),
                'code' => 'SUCCESS',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'ERROR',
                'error_message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }
}
