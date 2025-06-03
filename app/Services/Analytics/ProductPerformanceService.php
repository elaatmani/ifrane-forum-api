<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderConfirmationEnum;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductPerformanceService
{

    public function index(Request $request)
    {
        $exchange = request()->input('exchange', 4.88);
        $variant_charges = 1;
        // $created = self::parseDateRange('created');
        // $dropped = self::parseDateRange('dropped');
        // $delivered = self::parseDateRange('delivered');
        // $treated = self::parseDateRange('treated');
        $ads = self::parseDateRange('ads_at');

        $ads_query = "(select sum(a.spend) from ads a where a.product_id = order_items.product_id"
         . ($ads['from'] ? ' AND a.spent_in >= "' . $ads['from'] . '"' : '')
         . ($ads['to'] ? ' AND a.spent_in <= "' . $ads['to'] . '"' : '') . ')';


        $result = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('cities', 'orders.customer_city', '=', 'cities.name')
            ->join('products', 'order_items.product_id', '=', 'products.id')

            ->where('orders.agent_status', 'confirmed')
            ->whereIn('orders.delivery_status', ['settled', 'delivered'])

            ->select(
                'products.name',
                'order_items.product_id as product_id',
                DB::raw('count(order_items.order_id) as total_orders'),
                DB::raw('sum(order_items.quantity) as total_quantity'),
                DB::raw("sum($variant_charges) as variant_fees"),
                DB::raw("sum(order_items.price / $exchange) as total_sales"),
                DB::raw('sum(products.buying_price * order_items.quantity) as product_cost'),
                DB::raw("sum(cities.shipping_cost / $exchange) as shipping_fees"),
                DB::raw("COALESCE($ads_query, 0) as total_spent"),
                DB::raw("(sum(order_items.price / $exchange) - COALESCE($ads_query, 0) - sum(products.buying_price * order_items.quantity) - sum(cities.shipping_cost / $exchange) - sum(2) ) as net_profit")
            )
            ->groupBy('products.name', 'order_items.product_id')

            ->when(request()->input('sort.total_quantity', null), fn($q) => $q->orderBy('total_quantity', request()->input('sort.total_quantity', 'desc')))
            ->when(request()->input('sort.shipping_fees', null), fn($q) => $q->orderBy('shipping_fees', request()->input('sort.shipping_fees', 'desc')))
            ->when(request()->input('sort.product_cost', null), fn($q) => $q->orderBy('product_cost', request()->input('sort.product_cost', 'desc')))
            ->when(request()->input('sort.total_sales', null), fn($q) => $q->orderBy('total_sales', request()->input('sort.total_sales', 'desc')))
            ->when(request()->input('sort.total_orders', null), fn($q) => $q->orderBy('total_orders', request()->input('sort.total_orders', 'desc')))
            ->when(request()->input('sort.total_spent', null), fn($q) => $q->orderBy('total_spent', request()->input('sort.total_spent', 'desc')))

            ->orderBy('net_profit', request()->input('sort.net_profit', 'desc'))
            ->get()

            ->map(function ($r) {
                $r->total_spent = round($r->total_spent ? $r->total_spent : 0, 2);
                $r->total_orders = round($r->total_orders, 0);
                $r->total_quantity = round($r->total_quantity, 0);
                $r->total_sales = round($r->total_sales, 2);
                return $r;
            });

        $total_profit = $result->sum('net_profit');
        $total_ads_spend = $result->sum('total_spent');
        $total_sales = $result->sum('total_sales');

        // Convert collection to a simple array
        $resultArray = $result->toArray();

        // Pagination parameters
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);
        $total = count($resultArray);

        // Create a new LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator(
            array_slice($resultArray, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return [
            ...$paginator->toArray(),
            'total_profit' => $total_profit,
            'total_ads_spend' => $total_ads_spend,
            'total_sales' => $total_sales,
        ];
    }


    public static function parseDateRange($key)
    {
        $fromDate = data_get(request()->all(), $key . '.from', null);
        $toDate = data_get(request()->all(), $key . '.to', null);

        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

}
