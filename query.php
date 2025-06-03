<?php

// Start measuring execution time
$start_time = microtime(true);

try {
    // Get request parameters
    $params = $_REQUEST;
    
    // Get exchange rate from request or use default
    $exchange_rate = $params['exchange_rate'] ?? 4.88;
    
    // Date range filters
    $date_range = [
        'from' => $params['from'] ?? null,
        'to' => $params['to'] ?? null
    ];
    
    // Search parameter
    $search = $params['search'] ?? null;
    
    // Pagination parameters
    $page = $params['page'] ?? 1;
    $perPage = $params['per_page'] ?? 10;
    
    // Sorting parameters
    $sortBy = $params['sorting']['sortBy'] ?? 'total_orders';
    $sortDirection = $params['sorting']['sortDirection'] ?? 'desc';

    // Base product query with search
    $productQuery = DB::table('products')
        ->select('id', 'name', 'buying_price')
        ->whereNull('deleted_at');
        
    if ($search) {
        $productQuery->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('sku', 'LIKE', "%$search%");
        });
    }
    
    // Count total for pagination
    $total = $productQuery->count();
    
    // Get products for current page
    $products = clone $productQuery;
    $products = $products->get();
    $product_ids = $products->pluck('id')->toArray();
    
    // Prepare a map for quick lookup
    $product_map = [];
    foreach ($products as $product) {
        $product_map[$product->id] = [
            'id' => $product->id,
            'name' => $product->name,
            'total_orders' => 0,
            'total_delivered_orders' => 0,
            'total_confirmed_orders' => 0,
            'total_duplicated_orders' => 0,
            'total_turnover' => 0,
            'shipping_cost' => 0,
            'total_delivered_quantity' => 0,
            'total_delivered_product_cost' => 0,
            'total_spend' => 0,
            'total_profit' => 0,
            'profit_per_order' => 0
        ];
    }
    
    // COMBINED QUERY 1: Get orders, duplicated orders in a single query
    $orders_query = DB::table('orders as o')
        ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->whereIn('oi.product_id', $product_ids)
        ->whereNull('oi.deleted_at');
    
    $orders_data = $orders_query->select(
            'oi.product_id',
            DB::raw('COUNT(DISTINCT o.id) as total_orders'),
            DB::raw('SUM(CASE WHEN o.agent_status = "duplicate" THEN 1 ELSE 0 END) as duplicated_orders')
        )
        ->groupBy('oi.product_id')
        ->get();
    
    foreach ($orders_data as $order) {
        $product_map[$order->product_id]['total_orders'] = $order->total_orders;
        $product_map[$order->product_id]['total_duplicated_orders'] = $order->duplicated_orders;
    }
    
    // COMBINED QUERY 2: Get delivered orders, turnover, quantity, and shipping_cost in a single query
    $delivered_query = DB::table('orders as o')
        ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->leftJoin('cities as c', 'c.name', '=', 'o.customer_city')
        ->join('products as p', 'p.id', '=', 'oi.product_id')
        ->join('history as h', function($join) use ($date_range) {
            $join->on('h.trackable_id', '=', 'o.id')
                 ->whereRaw('JSON_CONTAINS(h.fields, \'{"field": "delivery_status", "new_value": "delivered"}\')');
                 
            // Apply date filters to history.created_at if provided
            if (!empty($date_range['from'])) {
                $join->where('h.created_at', '>=', $date_range['from']);
            }
            if (!empty($date_range['to'])) {
                $join->where('h.created_at', '<=', $date_range['to']);
            }
        })
        ->whereIn('oi.product_id', $product_ids)
        ->whereNull('oi.deleted_at');
    
    $delivered_data = $delivered_query->select(
            'oi.product_id',
            DB::raw('COUNT(DISTINCT o.id) as delivered_count'),
            DB::raw('SUM(oi.price) as turnover'),
            DB::raw('SUM(oi.quantity) as quantity'),
            DB::raw('SUM(c.shipping_cost) as shipping_cost'),
            DB::raw('SUM(p.buying_price * oi.quantity) as product_cost')
        )
        ->groupBy('oi.product_id')
        ->get();
    
    foreach ($delivered_data as $data) {
        $product_map[$data->product_id]['total_delivered_orders'] = $data->delivered_count;
        $product_map[$data->product_id]['total_turnover'] = $data->turnover;
        $product_map[$data->product_id]['total_delivered_quantity'] = (int)$data->quantity;
        $product_map[$data->product_id]['shipping_cost'] = (float)$data->shipping_cost;
        $product_map[$data->product_id]['total_delivered_product_cost'] = $data->product_cost;
    }
    
    // Get confirmed orders
    $confirmed_query = DB::table('orders as o')
        ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->join('history as h', function($join) use ($date_range) {
            $join->on('h.trackable_id', '=', 'o.id')
                 ->whereRaw('JSON_CONTAINS(h.fields, \'{"field": "agent_status", "new_value": "confirmed"}\')');
                 
            // Apply date filters to history.created_at if provided
            if (!empty($date_range['from'])) {
                $join->where('h.created_at', '>=', $date_range['from']);
            }
            if (!empty($date_range['to'])) {
                $join->where('h.created_at', '<=', $date_range['to']);
            }
        })
        ->whereIn('oi.product_id', $product_ids);
    
    $confirmed_orders = $confirmed_query->select('oi.product_id', DB::raw('COUNT(DISTINCT o.id) as confirmed_count'))
        ->groupBy('oi.product_id')
        ->get();
    
    foreach ($confirmed_orders as $order) {
        $product_map[$order->product_id]['total_confirmed_orders'] = $order->confirmed_count;
    }
    
    // Get ads spend
    $ads_query = DB::table('ads as a')
        ->whereIn('a.product_id', $product_ids)
        ->whereNull('a.deleted_at');
    
    // Apply date filters if provided
    if (!empty($date_range['from'])) {
        $ads_query->where('a.spent_in', '>=', $date_range['from']);
    }
    if (!empty($date_range['to'])) {
        $ads_query->where('a.spent_in', '<=', $date_range['to']);
    }
    
    $ads_spend = $ads_query->select('a.product_id', DB::raw('SUM(a.spend) as total_spend'))
        ->groupBy('a.product_id')
        ->get();
    
    foreach ($ads_spend as $spend) {
        $product_map[$spend->product_id]['total_spend'] = round($spend->total_spend, 2);
    }
    
    // Calculate profit for each product
    foreach ($product_map as $product_id => &$stats) {
        // Convert LYB to USD for turnover and shipping cost
        $turnover_in_usd = $stats['total_turnover'] / $exchange_rate;
        $shipping_cost_in_usd = $stats['shipping_cost'] / $exchange_rate;
        
        // Calculate profit in USD: turnover_in_usd - (spend + shipping_cost_in_usd + product_cost + delivery_fee)
        $delivery_fee = $stats['total_delivered_orders'] * 1; // $1 per delivered order
        
        $stats['total_profit'] = round($turnover_in_usd - ($stats['total_spend'] + $shipping_cost_in_usd + $stats['total_delivered_product_cost'] + $delivery_fee), 2);
        
        // Calculate profit per order
        $stats['profit_per_order'] = $stats['total_delivered_orders'] > 0 ? 
            round($stats['total_profit'] / $stats['total_delivered_orders'], 2) : 0;
    }
    
    // Convert to array and apply sorting
    $data = array_values($product_map);
    
    // Apply sorting
    usort($data, function($a, $b) use ($sortBy, $sortDirection) {
        if (!isset($a[$sortBy]) || !isset($b[$sortBy])) {
            return 0;
        }
        
        $comparison = 0;
        if (is_numeric($a[$sortBy]) && is_numeric($b[$sortBy])) {
            $comparison = $a[$sortBy] <=> $b[$sortBy];
        } else {
            $comparison = strcmp($a[$sortBy], $b[$sortBy]);
        }
        
        return $sortDirection === 'desc' ? -$comparison : $comparison;
    });
    
    // Apply pagination to the sorted data
    $offset = ($page - 1) * $perPage;
    $paginatedData = array_slice($data, $offset, $perPage);
    
    // Build pagination information
    $pagination = [
        'total' => $total,
        'per_page' => (int)$perPage,
        'current_page' => (int)$page,
        'last_page' => ceil($total / $perPage),
        'from' => $offset + 1,
        'to' => min($offset + $perPage, $total),
    ];
    
    // Calculate execution time
    $execution_time = microtime(true) - $start_time;
    
    // Return the data in JSON format
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $paginatedData,
        'execution_time' => $execution_time,
        ...$pagination
    ]);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}