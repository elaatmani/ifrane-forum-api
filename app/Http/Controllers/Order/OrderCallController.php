<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderCallController extends Controller
{
    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {

        $order = $this->repository->query()->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        $type = $request->input('type', 'agent');

        if ($type == 'agent') {
            $order->update([
                'calls' => $order->calls + 1,
            ]);
        } elseif ($type == 'followup') {
            $order->update([
                'followup_calls' => $order->followup_calls + 1,
            ]);
        }

        return response()->json([
            'message' => 'Order call updated',
            'code' => 'SUCCESS'
        ]);
    }
}
