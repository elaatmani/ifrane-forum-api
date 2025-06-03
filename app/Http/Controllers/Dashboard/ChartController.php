<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;

class ChartController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getDailyNewOrders(Request $request)
    {
        $data = cache()->remember('daily_new_orders', 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();

            $results = DB::table('orders')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');

            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
    }

    public function getDailyConfirmedOrders(Request $request)
    {
        $data = cache()->remember('daily_confirmed_orders', 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();
        
            $results = DB::table('history')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', [$start, $end])
                ->where('fields', 'LIKE', '%field":"agent_status","new_value":"confirmed"%')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');
        
            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
    }

    public function getDailyDeliveredOrders(Request $request)
    {
        $data = cache()->remember('daily_delivered_orders', 3600, function () {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();
        
            $results = DB::table('history')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', [$start, $end])
                ->where('fields', 'LIKE', '%field":"delivery_status","new_value":"delivered"%')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');
        
            return $this->generateDateRangeData($start, $end, $results);
        });

        return response()->json(['data' => $data, 'code' => 'SUCCESS']);
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