<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderConfirmationEnum;

class KPIsAnalyticsService
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $params = $request->all();

        $params = [
            'created_at' => $this->parseDateRange($params, 'created_at'),
            'dropped_at' => $this->parseDateRange($params, 'dropped_at'),
            'treated_at' => $this->parseDateRange($params, 'treated_at'),
            'delivered_at' => $this->parseDateRange($params, 'delivered_at'),
            'ads_at' => $this->parseDateRange($params, 'ads_at'),
            'agent_status' => $params['agent_status'] ?? [],
            'delivery_status' => $params['delivery_status'] ?? [],
            'agent_id' => $params['agent_id'] ?? [],
            'product_id' => $params['product_id'] ?? [],
        ];

        $results = [
            'total_ad_spend' => round($this->total_ad_spend($params), 2),
            'total_orders' => $this->total_orders($params),
            'total_duplicated_orders' => $this->total_duplicated_orders($params),
            'total_confirmed_orders' => $this->total_confirmed_orders($params),
            'total_delivered_orders' => $this->total_delivered_orders($params),
            'total_settled_orders' => $this->total_settled_orders($params),
            'total_turnover' => round($this->total_turnover($params), 2),
            'total_turnover_delivered' => round($this->total_turnover_delivered($params), 2),
            'total_turnover_settled' => round($this->total_turnover_settled($params), 2),
            'total_product_cost' => round($this->total_product_cost($params), 2),
            'total_shipping_cost' => round($this->total_shipping_cost($params), 2),
            'total_quantity' => $this->total_quantity($params),
        ];

        return $results;
    }


    public static function parseDateRange($params, $key)
    {
        $fromDate = data_get($params, $key . '.from', null);
        $toDate = data_get($params, $key . '.to', null);

        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    public function apply_filters(&$query, $params) {
        foreach($params as $key => $value) {
            if (in_array($key, ['created_at', 'dropped_at', 'treated_at', 'delivered_at', 'ads_at'])) {
                $this->apply_date_filter($query, $key, $value['from'], $value['to']);
            }
        }

        if (isset($params['agent_id']) && !empty($params['agent_id'])) {
            $query->whereIn('agent_id', $params['agent_id']);
        }

        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $query->whereIn('oi.product_id', $params['product_id']);
        }

        if (isset($params['delivery_status']) && !empty($params['delivery_status'])) {
            $query->whereIn('delivery_status', $params['delivery_status']);
        }
        
        if (isset($params['agent_status']) && !empty($params['agent_status'])) {
            $query->whereIn('agent_status', $params['agent_status']);
        }
    }


    public function total_orders($params)
    {
        $query = DB::table('orders as o')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->whereNull('oi.deleted_at')
        ->whereNull('o.deleted_at')
            ->where('agent_status', '!=', OrderConfirmationEnum::DUBPLICATE->value);

        $this->apply_filters($query, $params);

        return $query->distinct('o.id')->count('o.id');
    }

    public function total_duplicated_orders($params)
    {
        $query = DB::table('orders as o')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('agent_status', '=', OrderConfirmationEnum::DUBPLICATE->value);

        $this->apply_filters($query, $params);

        return $query->distinct('o.id')->count('o.id');
    }


    public function total_confirmed_orders($params)
    {
        $query = DB::table('orders as o')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->whereNull('oi.deleted_at')
        ->whereNull('o.deleted_at')
            ->where('agent_status', '=', OrderConfirmationEnum::CONFIRMED->value);

        $this->apply_filters($query, $params);

        return $query->distinct('o.id')->count('o.id');
    }

    public function total_delivered_orders($params)
    {
        $query = DB::table('orders as o')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
        ->whereNull('oi.deleted_at')
        ->whereNull('o.deleted_at')
            ->where('agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->where('delivery_status', '=', OrderDeliveryEnum::DELIVERED->value);

        $this->apply_filters($query, $params);

        return $query->distinct('o.id')->count('o.id');
    }

    public function total_settled_orders($params)
    {
        $query = DB::table('orders as o')
            ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->where('delivery_status', '=', OrderDeliveryEnum::SETTLED->value);

        $this->apply_filters($query, $params);

        return $query->distinct('o.id')->count('o.id');
    }

    public function total_ad_spend($params)
    {
        $from = $params['ads_at']['from'];

        $to = $params['ads_at']['to'];
        $query = DB::table('ads as a');
        
        $query->whereNull('a.deleted_at');

        $query->when($from, function ($subquery) use ($from) {
            return $subquery->whereDate('a.spent_in', '>=', $from);
        })

        ->when($to, function ($subquery) use ($to) {
            return $subquery->whereDate('a.spent_in', '<=', $to);
        });

        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $query->whereIn('a.product_id', $params['product_id']);
        }

        // $this->apply_filters($query, $params, 'ads');

        return $query->sum('spend');
    }


    public function total_quantity($params)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::SETTLED->value, OrderDeliveryEnum::DELIVERED->value]);

        $this->apply_filters($query, $params);

        return $query->sum('oi.quantity');
    }

    public function total_turnover($params)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::SETTLED->value, OrderDeliveryEnum::DELIVERED->value]);

        $this->apply_filters($query, $params);

        return $query->sum('oi.price');
    }

    public function total_turnover_delivered($params)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::DELIVERED->value]);

        $this->apply_filters($query, $params);

        return $query->sum('oi.price');
    }

    public function total_turnover_settled($params)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::SETTLED->value]);

        $this->apply_filters($query, $params);

        return $query->sum('oi.price');
    }

    public function total_product_cost($params)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::SETTLED->value, OrderDeliveryEnum::DELIVERED->value]);

        $this->apply_filters($query, $params);

        return $query->select(DB::raw('SUM(p.buying_price * oi.quantity) as total_cost'))
            ->value('total_cost');
    }


    public function total_shipping_cost($params)
    {
        $query = DB::table('orders as o')
        ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('cities as c', 'c.name', '=', 'o.customer_city')
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.agent_status', '=', OrderConfirmationEnum::CONFIRMED->value)
            ->whereIn('o.delivery_status', [OrderDeliveryEnum::SETTLED->value, OrderDeliveryEnum::DELIVERED->value]);

        $this->apply_filters($query, $params);

        return $query->sum('c.shipping_cost');
    }

    public function apply_date_filter(&$query, $key, $from, $to)
    {
        if (in_array($key, ['created_at'])) {
            $query->when($from, function ($subquery) use ($from) {
                return $subquery->whereDate('o.created_at', '>=', $from);
            })

            ->when($to, function ($subquery) use ($to) {
                return $subquery->whereDate('o.created_at', '<=', $to);
            });
        }

        if (in_array($key, ['dropped_at'])) {
            $query->when($to, function ($subquery) use ($to) {
                return $subquery->whereIn('o.id', function ($q) use ($to) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%field":"agent_id"%')
                        ->whereDate('created_at', '<=', $to);
                });
            })

            ->when($from, function ($subquery) use ($from) {
                return $subquery->whereIn('o.id', function ($q) use ($from) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%field":"agent_id"%')
                        ->whereDate('created_at', '>=', $from);
                });
            });
        }


        if (in_array($key, ['treated_at'])) {
            $query->when($to, function ($subquery) use ($to) {
                return $subquery->whereIn('o.id', function ($q) use ($to) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%field":"agent_status"%')
                        ->whereDate('created_at', '<=', $to);
                });
            })

            ->when($from, function ($subquery) use ($from) {
                return $subquery->whereIn('o.id', function ($q) use ($from) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%field":"agente_status"%')
                        ->whereDate('created_at', '>=', $from);
                });
            });
        }

        if (in_array($key, ['delivered_at'])) {
            $query->when($to, function ($subquery) use ($to) {
                return $subquery->whereIn('o.id', function ($q) use ($to) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%new_value":"delivered"%')
                        ->whereDate('created_at', '<=', $to);
                });
            })

            ->when($from, function ($subquery) use ($from) {
                return $subquery->whereIn('o.id', function ($q) use ($from) {
                    return $q->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'like', '%Order%')
                        ->where('fields', 'like', '%new_value":"delivered"%')
                        ->whereDate('created_at', '>=', $from);
                });
            });
        }
    }
}
