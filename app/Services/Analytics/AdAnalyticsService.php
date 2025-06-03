<?php

namespace App\Services\Analytics;

use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdAnalyticsService
{
    public function index(Request $request)
    {
        $params = $_REQUEST;

        // Get exchange rate from request or use default
        $exchange_rate = $params['exchange_rate'] ?? 4.88;

        $product_ids = $params['product_id'] ?? [];
        $marketer_ids = $params['marketer_id'] ?? [];

        // Date range filters
        $date_range = [
            'from' => data_get($params, 'spent_in.from') ?? null,
            'to' => data_get($params, 'spent_in.to') ?? null
        ];

        // Get total spent
        $totalSpent = DB::table('ads')
            ->when($date_range['from'], function ($query) use ($date_range) {
                $query->where('spent_in', '>=', $date_range['from']);
            })
            ->when($date_range['to'], function ($query) use ($date_range) {
                $query->where('spent_in', '<=', $date_range['to']);
            })
            ->when(!empty($product_ids), function ($query) use ($product_ids) {
                $query->whereIn('product_id', $product_ids);
            })
            ->when(!empty($marketer_ids), function ($query) use ($marketer_ids) {
                $query->whereIn('user_id', $marketer_ids);
            })
            ->whereNull('deleted_at')->sum('spend');
        
        // Get order-related totals in one query
        $orderTotals = DB::table('orders as o')
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('ads as a', function($join) use ($date_range, $product_ids, $marketer_ids) {
                $join->on('oi.product_id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
                    ->when($date_range['from'], function ($query) use ($date_range) {
                        $query->where('spent_in', '>=', $date_range['from']);
                    })
                    ->when($date_range['to'], function ($query) use ($date_range) {
                        $query->where('spent_in', '<=', $date_range['to']);
                    })
                    ->when(!empty($product_ids), function ($query) use ($product_ids) {
                        $query->whereIn('a.product_id', $product_ids);
                    })
                    ->when(!empty($marketer_ids), function ($query) use ($marketer_ids) {
                        $query->whereIn('a.user_id', $marketer_ids);
                    })
                    ->whereNull('a.deleted_at')
                    ->on('gs.marketer_id', '=', 'a.user_id');
            })
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->select([
                DB::raw('COUNT(DISTINCT o.id) as total_orders_count'),
                DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" THEN oi.price ELSE 0 END) as total_confirmed_turnover'),
                DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN oi.price ELSE 0 END) as total_delivered_turnover'),
                DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status = "confirmed" THEN o.id END) as total_confirmed_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN o.id END) as total_delivered_orders')
            ])
            ->first();
        
        // Get shipping costs total
        $shippingTotal = DB::table('orders as o')
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('cities as c', 'o.customer_city', '=', 'c.name')
            ->join('ads as a', function($join) use ($date_range, $product_ids, $marketer_ids) {
                $join->on('oi.product_id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
                    ->when($date_range['from'], function ($query) use ($date_range) {
                        $query->where('spent_in', '>=', $date_range['from']);
                    })
                    ->when($date_range['to'], function ($query) use ($date_range) {
                        $query->where('spent_in', '<=', $date_range['to']);
                    })
                    ->when(!empty($product_ids), function ($query) use ($product_ids) {
                        $query->whereIn('a.product_id', $product_ids);
                    })
                    ->when(!empty($marketer_ids), function ($query) use ($marketer_ids) {
                        $query->whereIn('a.user_id', $marketer_ids);
                    })
                    ->whereNull('a.deleted_at')
                    ->on('gs.marketer_id', '=', 'a.user_id');
            })
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', 'confirmed')
            ->whereIn('o.delivery_status', ['delivered', 'settled'])
            ->sum('c.shipping_cost');
        
        // Get product costs total
        $productCostTotal = DB::table('orders as o')
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('ads as a', function($join) use ($date_range, $product_ids, $marketer_ids) {
                $join->on('p.id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
                    ->when($date_range['from'], function ($query) use ($date_range) {
                        $query->where('spent_in', '>=', $date_range['from']);
                    })
                    ->when($date_range['to'], function ($query) use ($date_range) {
                        $query->where('spent_in', '<=', $date_range['to']);
                    })
                    ->when(!empty($product_ids), function ($query) use ($product_ids) {
                        $query->whereIn('a.product_id', $product_ids);
                    })
                    ->when(!empty($marketer_ids), function ($query) use ($marketer_ids) {
                        $query->whereIn('a.user_id', $marketer_ids);
                    })
                    ->whereNull('a.deleted_at')
                    ->on('gs.marketer_id', '=', 'a.user_id');
            })
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', 'confirmed')
            ->whereIn('o.delivery_status', ['delivered', 'settled'])
            ->sum('p.buying_price');

        $costPerLead = $orderTotals->total_orders_count > 0 ? $totalSpent / $orderTotals->total_orders_count : 0;

        
        // Combine all totals
        $totals = [
            'total_orders_count' => $orderTotals->total_orders_count ?? 0,
            'total_confirmed_turnover' => $orderTotals->total_confirmed_turnover ?? 0,
            'total_delivered_turnover' => $orderTotals->total_delivered_turnover ?? 0,
            'total_confirmed_orders' => $orderTotals->total_confirmed_orders ?? 0,
            'total_delivered_orders' => $orderTotals->total_delivered_orders ?? 0,
            'total_shipping_cost' => $shippingTotal ?? 0,
            'total_product_cost' => $productCostTotal ?? 0,
            'total_spent' => $totalSpent ?? 0,
            'cost_per_lead' => $costPerLead ?? 0,
        ];

        // Return the data in JSON format
        return $totals;
    }

    public function leadsByRange(Request $request) {
        $params = $_REQUEST;

        // Get date range from request or default to last 7 days
        $to = data_get($params, 'spent_in.to') ? Carbon::parse(data_get($params, 'spent_in.to')) : Carbon::now()->subDays(1);
        $from = data_get($params, 'spent_in.from') ? Carbon::parse(data_get($params, 'spent_in.from')) : $to->copy()->subDays(7);

        // Ensure to is always the end of day and from is start of day
        $to = $to->endOfDay();
        $from = $from->startOfDay();

        // Get product and marketer filters if present
        $product_ids = $params['product_id'] ?? [];
        $marketer_ids = $params['marketer_id'] ?? [];

        // Create a date range for all days in the range
        $dateRange = [];
        $currentDate = $from->copy();
        
        while ($currentDate->lte($to)) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Query leads data from ads table
        $leads = DB::table('ads')
            ->selectRaw('DATE(spent_in) as date, SUM(leads) as total_leads')
            ->whereNull('deleted_at')
            ->whereDate('spent_in', '>=', $from)
            ->whereDate('spent_in', '<=', $to)
            ->when(!empty($product_ids), function ($query) use ($product_ids) {
                $query->whereIn('product_id', $product_ids);
            })
            ->when(!empty($marketer_ids), function ($query) use ($marketer_ids) {
                $query->whereIn('user_id', $marketer_ids);
            })
            ->groupBy(DB::raw('DATE(spent_in)'))
            ->get()
            ->keyBy('date');

        // Prepare result with all days (including days with 0 leads)
        $result = [];
        foreach ($dateRange as $date) {
            $result[] = [
                'date' => $date,
                'total_leads' => isset($leads[$date]) ? (int) $leads[$date]->total_leads : 0
            ];
        }

        return $result;
    }
}