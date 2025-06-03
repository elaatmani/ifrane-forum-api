<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\OrderRepository;

class AgentDashboardController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function performance(Request $request)
    {

        $results = $this->orderRepository->getOrderStatusForAgent(auth()->user()->id);

        return response()->json([
            'data' => $results,
            'code' => 'SUCCESS',
        ]);
    }

    public function getDailyDroppedOrders(Request $request)
    {

        $data = cache()->remember('daily_dropped_orders_' . auth()->id(), 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();

            $results = DB::table('history')
                ->join('orders', 'orders.id', '=', 'history.trackable_id')
                ->selectRaw('DATE(history.created_at) as date, COUNT(*) as count')
                ->whereBetween('history.created_at', [$start, $end])
                ->where('fields', 'LIKE', '%"old_value":"new"%')
                ->where('orders.agent_id', auth()->user()->id)
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');

            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
    }

    public function getDailyTreatedOrders(Request $request)
    {

        $data = cache()->remember('daily_treated_orders_' . auth()->id(), 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();

            $results = DB::table('history')
                ->join('orders', 'orders.id', '=', 'history.trackable_id')
                ->selectRaw('DATE(history.created_at) as date, COUNT(*) as count')
                ->whereBetween('history.created_at', [$start, $end])
                ->where('fields', 'LIKE', '%field":"agent_status"%')
                ->where('orders.agent_id', auth()->user()->id)
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');

            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
    }

    public function getDailyConfirmedOrders(Request $request)
    {

        $data = cache()->remember('daily_confirmed_orders_' . auth()->id(), 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();

            $results = DB::table('history')
                ->join('orders', 'orders.id', '=', 'history.trackable_id')
                ->selectRaw('DATE(history.created_at) as date, COUNT(*) as count')
                ->whereBetween('history.created_at', [$start, $end])
                ->where('fields', 'LIKE', '%field":"agent_status","new_value":"confirmed"%')
                ->where('orders.agent_id', auth()->user()->id)
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');

            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
    }

    public function getDailyDeliveredOrders(Request $request)
    {

        $results = [];

        return response()->json([
            'data' => $results,
            'code' => 'SUCCESS',
        ]);
    }

    public function topProductConfirmation(Request $request)
    {
        $results = $this->orderRepository->getProductsConfirmationByAgent(auth()->id());
        $data = [];

        foreach ($results as $result) {
            $productId = $result->id;

            if (!isset($data[$productId])) {
                $data[$productId] = [
                    'id' => $productId,
                    'product_name' => $result->name,
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'confirmation_rate' => 0,
                    'agent_status' => [],
                ];
            }

            // Add total orders
            $data[$productId]['total_orders'] += $result->total_orders;

            // Add confirmed orders
            if ($result->agent_status === 'confirmed') {
                $data[$productId]['confirmed_orders'] += $result->total_orders;
            }

            // Store agent status breakdown
            $data[$productId]['agent_status'][] = [
                'agent_status' => $result->agent_status,
                'count' => $result->total_orders
            ];
        }

        // Calculate confirmation rate
        foreach ($data as &$item) {
            $item['confirmation_rate'] = $item['total_orders'] > 0
                ? round(($item['confirmed_orders'] / $item['total_orders']) * 100, 2)
                : 0;
        }

        $data = collect($data)->sortByDesc('confirmed_orders')->values();


        return response()->json([
            'data' => $data,
            'code' => 'SUCCESS',
        ]);
    }

    protected function generateDateRangeData($start, $end, $results)
    {
        $period = CarbonPeriod::create($start, '1 day', $end);

        return collect($period)->map(function ($date) use ($results) {
            $dateStr = $date->format('Y-m-d');
            return [
                'date' => $dateStr,
                'count' => $results->get($dateStr, 0)
            ];
        })->values();
    }
}
