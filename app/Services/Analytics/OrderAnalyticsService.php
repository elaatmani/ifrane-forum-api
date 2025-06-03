<?php

namespace App\Services\Analytics;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Enums\OrderDeliveryEnum;
use App\Enums\NawrisOrderTypeEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderConfirmationEnum;
use Illuminate\Support\Facades\Http;



class OrderAnalyticsService
{

    public function getRevenue($params)
    {
        $created_at_from = data_get($params, 'created_at.from');
        $created_at_to = data_get($params, 'created_at.to');



        if ($created_at_from) {
            $created_at_from = Carbon::parse($created_at_from);
        }

        if ($created_at_to) {
            $created_at_to = Carbon::parse($created_at_to);
        }

        $totalEarning =  DB::table('order_items')
            ->whereNull('order_items.deleted_at')

            ->when($created_at_from, function ($query) use ($created_at_from) {
                $query->whereDate('order_items.created_at', '>=', $created_at_from)->get();
            })
            ->when($created_at_to, function ($query) use ($created_at_to) {
                $query->whereDate('order_items.created_at', '<=', $created_at_to)->get();
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.agent_status', [OrderConfirmationEnum::CONFIRMED->value, OrderConfirmationEnum::CHANGE->value])
            ->whereIn('orders.delivery_status', [OrderDeliveryEnum::DELIVERED->value, OrderDeliveryEnum::SETTLED->value])
            ->sum('order_items.price');

        return $totalEarning;
    }

    public function getConfrimationCount($params = [])
    {
        $created_at = self::getDateFromParams($params, 'created_at');
        $treated_at = self::getDateFromParams($params, 'treated_at');
        $dropped_at = self::getDateFromParams($params, 'dropped_at');
        $delivered_at = self::getDateFromParams($params, 'delivered_at');

        $agent_status = data_get($params, 'agent_status', []);
        $agent_id = data_get($params, 'agent_id', []);
        $delivery_status = data_get($params, 'delivery_status', []);
        $product_ids = data_get($params, 'product_id', []);


        $confirmations = DB::table('orders')
            ->when(count($agent_status) > 0, function ($query) use ($agent_status) {
                $query->whereIn('agent_status', $agent_status);
            })
            ->when(count($delivery_status) > 0, function ($query) use ($delivery_status) {
                $query->whereIn('delivery_status', $delivery_status);
            })
            ->when(count($agent_id) > 0, function ($query) use ($agent_id) {
                $query->whereIn('agent_id', $agent_id);
            })
            ->when($created_at['from'], function ($query) use ($created_at) {
                $query->whereDate('created_at', '>=', $created_at['from']);
            })
            ->when($created_at['to'], function ($query) use ($created_at) {
                $query->whereDate('created_at', '<=', $created_at['to']);
            })

            ->when(count($product_ids) > 0, function ($query) use ($product_ids) {
                $query->whereIn('orders.id', function ($subQuery) use ($product_ids) {
                    $subQuery->select('order_items.order_id')
                        ->from('order_items')
                        ->whereNull('order_items.deleted_at')
                        ->whereIn('product_id', $product_ids);
                });
            })

            ->when($treated_at['from'] || $treated_at['to'], function ($query) use ($treated_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($treated_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where(function ($q) {
                            $q->where('fields', 'like', '%agent_status%')
                                ->orWhere('fields', 'like', '%"calls"%');
                        })
                        ->whereRaw('history.actor_id = orders.agent_id')
                        ->when($treated_at['from'], function ($q) use ($treated_at) {
                            $q->whereDate('created_at', '>=', $treated_at['from']);
                        })
                        ->when($treated_at['to'], function ($q) use ($treated_at) {
                            $q->whereDate('created_at', '<=', $treated_at['to']);
                        });
                });
            })

            ->when($delivered_at['from'] || $delivered_at['to'], function ($query) use ($delivered_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($delivered_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where(function ($q) {
                            $q->where('fields', 'like', '%"new_value":"delivered"%');
                        })
                        ->when($delivered_at['from'], function ($q) use ($delivered_at) {
                            $q->whereDate('created_at', '>=', $delivered_at['from']);
                        })
                        ->when($delivered_at['to'], function ($q) use ($delivered_at) {
                            $q->whereDate('created_at', '<=', $delivered_at['to']);
                        });
                });
            })

            ->when($dropped_at['from'] || $dropped_at['to'], function ($query) use ($dropped_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($dropped_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where('fields', 'like', '%agent_id%')
                        ->whereRaw('history.actor_id = orders.agent_id')
                        ->when($dropped_at['from'], function ($q) use ($dropped_at) {
                            $q->whereDate('created_at', '>=', $dropped_at['from']);
                        })
                        ->when($dropped_at['to'], function ($q) use ($dropped_at) {
                            $q->whereDate('created_at', '<=', $dropped_at['to']);
                        });
                });
            })
            


            ->select('agent_status', DB::raw('count(*) as count'))
            ->groupBy('agent_status')
            ->get();

        return $confirmations;
    }

    public static function getDeliveryCount($params = [])
    {
        $created_at = self::getDateFromParams($params, 'created_at');
        $treated_at = self::getDateFromParams($params, 'treated_at');
        $dropped_at = self::getDateFromParams($params, 'dropped_at');
        $delivered_at = self::getDateFromParams($params, 'delivered_at');

        $agent_status = data_get($params, 'agent_status', []);
        $agent_id = data_get($params, 'agent_id', []);
        $delivery_status = data_get($params, 'delivery_status', []);
        $product_ids = data_get($params, 'product_id', []);


        $delivery = DB::table('orders')
            ->when(count($agent_status) > 0, function ($query) use ($agent_status) {
                $query->whereIn('agent_status', $agent_status);
            })
            ->when(count($delivery_status) > 0, function ($query) use ($delivery_status) {
                $query->whereIn('delivery_status', $delivery_status);
            })
            ->when(count($agent_id) > 0, function ($query) use ($agent_id) {
                $query->whereIn('agent_id', $agent_id);
            })
            ->when($created_at['from'], function ($query) use ($created_at) {
                $query->whereDate('created_at', '>=', $created_at['from']);
            })
            ->when($created_at['to'], function ($query) use ($created_at) {
                $query->whereDate('created_at', '<=', $created_at['to']);
            })

            ->when(count($product_ids) > 0, function ($query) use ($product_ids) {
                $query->whereIn('orders.id', function ($subQuery) use ($product_ids) {
                    $subQuery->select('order_items.order_id')
                        ->from('order_items')
                        ->whereNull('order_items.deleted_at')
                        ->whereIn('product_id', $product_ids);
                });
            })

            ->when($treated_at['from'] || $treated_at['to'], function ($query) use ($treated_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($treated_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where(function ($q) {
                            $q->where('fields', 'like', '%agent_status%')
                                ->orWhere('fields', 'like', '%"calls"%');
                        })
                        ->whereRaw('history.actor_id = orders.agent_id')
                        ->when($treated_at['from'], function ($q) use ($treated_at) {
                            $q->whereDate('created_at', '>=', $treated_at['from']);
                        })
                        ->when($treated_at['to'], function ($q) use ($treated_at) {
                            $q->whereDate('created_at', '<=', $treated_at['to']);
                        });
                });
            })

            ->when($delivered_at['from'] || $delivered_at['to'], function ($query) use ($delivered_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($delivered_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where(function ($q) {
                            $q->where('fields', 'like', '%"new_value":"delivered"%');
                        })
                        ->when($delivered_at['from'], function ($q) use ($delivered_at) {
                            $q->whereDate('created_at', '>=', $delivered_at['from']);
                        })
                        ->when($delivered_at['to'], function ($q) use ($delivered_at) {
                            $q->whereDate('created_at', '<=', $delivered_at['to']);
                        });
                });
            })

            ->when($dropped_at['from'] || $dropped_at['to'], function ($query) use ($dropped_at) {
                $query->whereIn('orders.id', function ($subQuery) use ($dropped_at) {
                    $subQuery->select('trackable_id')
                        ->from('history')
                        ->where('trackable_type', 'App\\Models\\Order')
                        ->where('fields', 'like', '%agent_id%')
                        ->whereRaw('history.actor_id = orders.agent_id')
                        ->when($dropped_at['from'], function ($q) use ($dropped_at) {
                            $q->whereDate('created_at', '>=', $dropped_at['from']);
                        })
                        ->when($dropped_at['to'], function ($q) use ($dropped_at) {
                            $q->whereDate('created_at', '<=', $dropped_at['to']);
                        });
                });
            })

            ->whereIn('agent_status', [OrderConfirmationEnum::CONFIRMED->value, OrderConfirmationEnum::CHANGE->value])
            ->select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->get();

        return $delivery;
    }

    public static function getOrdersCountByDays($from = null, $to = null)
    {
        $from = $from ?? now()->subDays(7)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $query = DB::table('orders')
            ->where('agent_status', '!=', OrderConfirmationEnum::DUBPLICATE->value)
            ->select(DB::raw('DATE(google_sheet_order_date) as date'), DB::raw('count(*) as count'))
            ->whereBetween('google_sheet_order_date', [$from, $to])
            ->groupBy(DB::raw('DATE(google_sheet_order_date)'))
            ->orderBy('date', 'asc');

        // Generate an array of dates in the range
        $period = new \DatePeriod(
            new \DateTime($from),
            new \DateInterval('P1D'),
            (new \DateTime($to))->modify('+0 day')
        );

        $result = $query->get();

        $dateCounts = [];
        foreach ($result as $result) {
            $dateCounts[$result->date] = $result->count;
        }

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (!isset($dateCounts[$dateStr])) {
                $dateCounts[$dateStr] = 0;
            }
        }

        // Sort the dates in ascending order
        ksort($dateCounts);

        // Prepare the final result
        $finalResult = [];
        foreach ($dateCounts as $date => $count) {
            $finalResult[] = (object) ['date' => $date, 'count' => $count];
        }

        return $finalResult;
    }

    public static function getKpis($params)
    {


        return [];
    }

    public static function getDateFromParams($params, $key)
    {
        $dateFrom = data_get($params, "$key.from");
        $dateTo = data_get($params, "$key.to");

        return [
            'from' => $dateFrom  ? Carbon::parse($dateFrom)->format('Y-m-d') : null,
            'to' => $dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : null,
        ];
    }
}
