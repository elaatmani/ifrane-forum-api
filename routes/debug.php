<?php

use Carbon\Carbon;
use App\Models\City;
use App\Models\User;
use App\Events\TestEvent;
use Illuminate\Http\Request;
use App\Services\NawrisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Services\Analytics\KPIsAnalyticsService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/test', function () {
    $start = DB::table('history')
    ->whereRaw('JSON_CONTAINS(fields, ?)', ['{"field": "nawris_code", "old_value": null}'])
    ->latest()->first('created_at');

    $end = DB::table('history')
    ->whereRaw('JSON_CONTAINS(fields, ?)', ['{"field": "delivery_status", "new_value": "delivered"}'])
    ->latest()->first('created_at');

    return (new Carbon($start))->diffForHumans(new Carbon($end));
    
    return 'test';
});


Route::get('/token/{password}', function(Request $request, $password) {

    if($password != 'x1234x5678x') return response()->json([ 'message' => 'Not Allowed' ], 403);

    $user = User::where('id', NawrisService::id())->first();
    $token = $user->createToken('API')->plainTextToken;

    return response()->json([
        'token' => $token
    ]);
});



Route::get('/fix-cities', function () {
    $nawrisCities = collect(NawrisService::cities()['feed'])->keyBy('id');
    $appCities = City::all();

    $fixed = [];

    foreach ($appCities as $city) {
        $nawrisCity = $nawrisCities->get($city->nawris_city_id);

        if ($nawrisCity && trim($city->name) !== trim($nawrisCity['name'])) {
            $oldName = $city->name;
            $city->name = $nawrisCity['name'];
            $city->save();

            $fixed[] = [
                'app_city_id' => $city->id,
                'nawris_city_id' => $city->nawris_city_id,
                'old_name' => $oldName,
                'new_name' => $city->name
            ];
        }
    }

    return response()->json([
        'status' => 'done',
        'updated_cities' => $fixed
    ]);
});


Route::get('/delivered-orders', function (Request $request) {
    // Check if date filtering should be applied
    $applyDateFilter = $request->has('start_date') || $request->has('end_date');
    
    // Set date range parameters if filtering is enabled
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    
    // Convert to Carbon instances if dates are provided
    $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
    $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    
    // Build delivered orders subquery
    $deliveredOrdersQuery = DB::table('history as h1')
        ->select('h1.trackable_id as id')
        ->where('h1.trackable_type', 'App\\Models\\Order')
        ->whereJsonContains('h1.fields', '{"field": "delivery_status", "new_value": "delivered"}')
        ->distinct();
    
    // Apply date filtering to delivered orders if requested
    if ($applyDateFilter) {
        if ($start) {
            $deliveredOrdersQuery->where('h1.created_at', '>=', $start);
        }
        if ($end) {
            $deliveredOrdersQuery->where('h1.created_at', '<=', $end);
        }
    }
    
    // Build confirmed orders subquery
    $confirmedOrdersQuery = DB::table('history as h2')
        ->select('h2.trackable_id as id')
        ->where('h2.trackable_type', 'App\\Models\\Order')
        ->whereJsonContains('h2.fields', '{"field": "agent_status", "new_value": "confirmed"}')
        ->distinct();
    
    // Apply date filtering to confirmed orders if requested
    if ($applyDateFilter) {
        if ($start) {
            $confirmedOrdersQuery->where('h2.created_at', '>=', $start);
        }
        if ($end) {
            $confirmedOrdersQuery->where('h2.created_at', '<=', $end);
        }
    }
    
    // Query to get products with order statistics using joins
    $productsData = DB::table('products as p')
        ->select([
            'p.id as product_id',
            'p.name as product_name',
            'p.sku as product_sku',
            DB::raw('COUNT(DISTINCT delivered_orders.id) as delivered_orders_count'),
            DB::raw('COUNT(DISTINCT confirmed_orders.id) as confirmed_orders_count'),
            DB::raw('SUM(CASE WHEN delivered_orders.id IS NOT NULL THEN oi.quantity ELSE 0 END) as total_delivered_quantity'),
            DB::raw('SUM(CASE WHEN confirmed_orders.id IS NOT NULL THEN oi.quantity ELSE 0 END) as total_confirmed_quantity'),
            DB::raw('SUM(CASE WHEN delivered_orders.id IS NOT NULL THEN oi.quantity * oi.price ELSE 0 END) as total_delivered_value'),
            DB::raw('SUM(CASE WHEN confirmed_orders.id IS NOT NULL THEN oi.quantity * oi.price ELSE 0 END) as total_confirmed_value')
        ])
        ->join('order_items as oi', 'p.id', '=', 'oi.product_id')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        // Left join to get delivered orders
        ->leftJoinSub($deliveredOrdersQuery, 'delivered_orders', 'o.id', '=', 'delivered_orders.id')
        // Left join to get confirmed orders
        ->leftJoinSub($confirmedOrdersQuery, 'confirmed_orders', 'o.id', '=', 'confirmed_orders.id')
        // Filter to only include confirmed or delivered orders
        ->whereRaw('delivered_orders.id IS NOT NULL OR confirmed_orders.id IS NOT NULL')
        ->whereNull('o.deleted_at')
        ->whereNull('oi.deleted_at')
        ->whereNull('p.deleted_at')
        ->groupBy('p.id', 'p.name', 'p.sku')
        ->orderByRaw('COUNT(DISTINCT delivered_orders.id) + COUNT(DISTINCT confirmed_orders.id) DESC')
        ->get();
    
    // Calculate conversion rates for each product
    $productsWithRates = $productsData->map(function($product) {
        $product->conversion_rate = $product->confirmed_orders_count > 0 
            ? round(($product->delivered_orders_count / $product->confirmed_orders_count) * 100, 2) 
            : 0;
        return $product;
    });
    
    // Prepare response
    $response = [
        'totals' => [
            'products' => $productsWithRates->count(),
            'delivered_orders' => $productsWithRates->sum('delivered_orders_count'),
            'confirmed_orders' => $productsWithRates->sum('confirmed_orders_count'),
            'delivered_quantity' => $productsWithRates->sum('total_delivered_quantity'),
            'confirmed_quantity' => $productsWithRates->sum('total_confirmed_quantity'),
            'delivered_value' => $productsWithRates->sum('total_delivered_value'),
            'confirmed_value' => $productsWithRates->sum('total_confirmed_value'),
            'overall_conversion_rate' => $productsWithRates->sum('confirmed_orders_count') > 0 
                ? round(($productsWithRates->sum('delivered_orders_count') / $productsWithRates->sum('confirmed_orders_count')) * 100, 2) 
                : 0
        ],
        'products' => $productsWithRates
    ];
    
    // Add date range info if filter was applied
    if ($applyDateFilter) {
        $response['date_range'] = [
            'start' => $start ? $start->toDateString() : 'all',
            'end' => $end ? $end->toDateString() : 'all',
        ];
        $response['date_filtered'] = true;
    } else {
        $response['date_filtered'] = false;
    }
    
    return response()->json($response);
});


Route::get('v', function() {
    $ads = DB::table('ads')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

    $results = [];
    
    foreach ($ads as $ad) {
        $spentIn = Carbon::parse($ad->spent_in)->format('Y-m-d');
    
        $leads = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('google_sheets', 'orders.google_sheet_id', '=', 'google_sheets.id')
            ->where('order_items.product_id', $ad->product_id)
            ->where('orders.google_sheet_order_date', 'like', $spentIn . '%')
            ->where('google_sheets.marketer_id', $ad->user_id)
            ->count();
            
    
        $results[] = [
            'ad_id' => $ad->id,
            'product_id' => $ad->product_id,
            'spent_in' => $spentIn,
            'leads' => $leads,
        ];
        
        // Update the 'leads' field for this ad
        DB::table('ads')
            ->where('id', $ad->id)
            ->update(['leads' => $leads]);
        }
        
    DB::table('ads')
    ->orderBy('id')
    ->chunkById(100, function ($ads) {
        foreach ($ads as $ad) {
            $spentIn = Carbon::parse($ad->spent_in)->format('Y-m-d');

            $leads = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('google_sheets', 'orders.google_sheet_id', '=', 'google_sheets.id')
                ->where('order_items.product_id', $ad->product_id)
                ->where('orders.google_sheet_order_date', 'like', $spentIn . '%')
                ->where('google_sheets.marketer_id', $ad->user_id)
                ->count();

            DB::table('ads')
                ->where('id', $ad->id)
                ->update(['leads' => $leads]);
        }
    });
        
        
    return response()->json([
        'data' => $results   
    ]); 
});

Route::get('/test-ads', function(Request $request) {
    // Check if totals should be calculated (default to false)
    $includeTotals = $request->input('include_totals', false);
    
    // Get the limited ads data with all calculated fields
    $ads = DB::table('ads as a')
        ->select([
            'a.*',
            DB::raw('DATE(a.spent_in) as dd'),
            // orders_count subquery
            DB::raw('(
                SELECT COUNT(*) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id
            ) as orders_count'),
            
            // confirmed_turnover subquery
            DB::raw('(
                SELECT COALESCE(SUM((
                    SELECT SUM(oi.price) FROM order_items oi 
                    WHERE oi.order_id = o.id AND oi.deleted_at IS NULL
                )), 0) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
            ) as confirmed_turnover'),
            
            // delivered_turnover subquery
            DB::raw('(
                SELECT COALESCE(SUM((
                    SELECT SUM(oi.price) FROM order_items oi 
                    WHERE oi.order_id = o.id AND oi.deleted_at IS NULL
                )), 0) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
                AND o.delivery_status IN ("delivered", "settled")
            ) as delivered_turnover'),
            
            // confirmed_orders subquery
            DB::raw('(
                SELECT COUNT(*) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
            ) as confirmed_orders'),
            
            // delivered_orders subquery
            DB::raw('(
                SELECT COUNT(*) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
                AND o.delivery_status IN ("delivered", "settled")
            ) as delivered_orders'),
            
            // shipping_cost subquery
            DB::raw('(
                SELECT COALESCE(SUM(c.shipping_cost), 0) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                JOIN cities c ON o.customer_city = c.name
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
                AND o.delivery_status IN ("delivered", "settled")
            ) as shipping_cost'),
            
            // product_cost subquery
            DB::raw('(
                SELECT COALESCE(SUM((
                    SELECT SUM(p.buying_price) FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = o.id AND p.id = a.product_id AND oi.deleted_at IS NULL
                )), 0) FROM orders o 
                JOIN google_sheets gs ON o.google_sheet_id = gs.id
                WHERE o.id IN (
                    SELECT oi.order_id FROM order_items oi 
                    WHERE oi.product_id = a.product_id AND oi.deleted_at IS NULL
                ) 
                AND DATE(o.google_sheet_order_date) = DATE(a.spent_in) 
                AND gs.marketer_id = a.user_id 
                AND o.agent_status = "confirmed"
                AND o.delivery_status IN ("delivered", "settled")
            ) as product_cost')
        ])
        ->orderBy('a.id', 'desc')
        ->limit(10)
        ->get();
    
    $response = ['data' => $ads];
    
    // Calculate totals only if requested
    if ($includeTotals) {
        // Use an optimized approach for totals
        // Instead of calculating totals for each ad and then summing,
        // we'll directly calculate the aggregates in one go
        
        // Get total spent
        $totalSpent = DB::table('ads')->sum('spent');
        
        // Get order-related totals in one query
        $orderTotals = DB::table('orders as o')
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('ads as a', function($join) {
                $join->on('oi.product_id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
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
            ->join('ads as a', function($join) {
                $join->on('oi.product_id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
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
            ->join('ads as a', function($join) {
                $join->on('p.id', '=', 'a.product_id')
                    ->whereRaw('DATE(o.google_sheet_order_date) = DATE(a.spent_in)')
                    ->whereNull('a.deleted_at')
                    ->on('gs.marketer_id', '=', 'a.user_id');
            })
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', 'confirmed')
            ->whereIn('o.delivery_status', ['delivered', 'settled'])
            ->sum('p.buying_price');
        
        // Combine all totals
        $totals = [
            'total_orders_count' => $orderTotals->total_orders_count ?? 0,
            'total_confirmed_turnover' => $orderTotals->total_confirmed_turnover ?? 0,
            'total_delivered_turnover' => $orderTotals->total_delivered_turnover ?? 0,
            'total_confirmed_orders' => $orderTotals->total_confirmed_orders ?? 0,
            'total_delivered_orders' => $orderTotals->total_delivered_orders ?? 0,
            'total_shipping_cost' => $shippingTotal ?? 0,
            'total_product_cost' => $productCostTotal ?? 0,
            'total_spent' => $totalSpent ?? 0
        ];
        
        $response['totals'] = $totals;
    }
    
    return response()->json($response);
});
