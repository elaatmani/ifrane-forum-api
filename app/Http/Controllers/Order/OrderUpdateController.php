<?php

namespace App\Http\Controllers\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\Order\OrderListResource;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Arr;

class OrderUpdateController extends Controller
{

    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateOrderRequest $request, $id)
    {
        $can_be_updated = [
            'customer_name',
            'customer_phone',
            'customer_address',
            'customer_city',
            'customer_area',
            'customer_notes',
            'agent_status',
            'agent_notes',
            'followup_status',
            'followup_calls',
            'delivery_status',
            'delivery_id',
            'order_sent_at',
            'order_delivered_at',
            'calls',
            'items',
            'nawris_code',
            'return_reason',
            'cancellation_reason',
            'cancellation_notes'
        ];

        try {
            // Filter only the fields that can be updated
            $fields = array_intersect_key($request->all(), array_flip($can_be_updated));
            $fields = Arr::only($fields, $request->fields);

            // Update the order with the filtered fields
            $order = $this->repository->update($id, $fields);
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
