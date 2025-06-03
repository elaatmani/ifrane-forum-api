<?php

namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use App\Services\NawrisService;
use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderDeliveryUpdateController extends Controller
{

    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            // DB::beginTransaction();

            $options = config('status.delivery.options');
            $to_status_code = $request->to_status_code;
            $order_code = $request->order_code;
            $return_reason = $request->return_reason;

            if (!isset($options[$to_status_code])) {
                return response()->json([
                    'message' => 'code status is not valid.',
                    'status_code' => $to_status_code
                ], 422);
            }

            $order = $this->repository->query()->where([
                'nawris_code' => $order_code,
                'delivery_id' => NawrisService::id()
            ])->first();

            if (!$order) {
                return response()->json([
                    'message' => 'order not found.',
                    'order_code' => $order_code
                ], 404);
            }



            $new_delivery = $options[$to_status_code];
            $old_delivery = $order->delivery_status;

            $order->update([
                'return_reason' => $return_reason,
                'delivery_status' => $new_delivery
            ]);

            // DB::commit();

            return response()->json([
                'message' => 'order updated successfully.',
                'order_code' => $order_code,
                'new_status' => $new_delivery,
                'old_status' => $old_delivery
            ], 200);
        } catch (\Throwable $th) {
            // DB::rollBack();

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
