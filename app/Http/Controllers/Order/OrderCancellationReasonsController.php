<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OrderCancellationReasonEnum;

class OrderCancellationReasonsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $options = config('status.cancellation_reasons.options');
        
        return response()->json([
            'data' => $options,
            'code' => 'SUCCESS',
        ], 200);
    }
} 