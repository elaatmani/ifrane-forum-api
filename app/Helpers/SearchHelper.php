<?php 


namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enums\OrderConfirmationEnum;


class SearchHelper {
    public static function order(Request $request)
    {
        $filters = $request->all();
        $criteria = [];

        if($search = $request->input('search', null)) {
            $criteria['orWhere'][] = [ 'field' => 'nawris_code', 'operator' => 'LIKE', 'value' => '%' . (string)  $search . '%' ];
            $criteria['orWhere'][] = [ 'field' => 'id', 'operator' => 'LIKE', 'value' => '%' . (string)  $search . '%' ];
            $criteria['orWhere'][] = [ 'field' => 'customer_city', 'operator' => 'LIKE', 'value' => '%' . (string)  $search . '%' ];
            $criteria['orWhere'][] = [ 'field' => 'customer_phone', 'operator' => 'LIKE', 'value' => '%' . (string)  $search . '%' ];
            $criteria['orWhere'][] = [ 'field' => 'customer_name', 'operator' => 'LIKE', 'value' => '%' . (string)  $search . '%' ];

        }

        if($agent_status = $request->input('filters.agent_status', null)) {
            $criteria['whereIn'][] = [ 'field' => 'agent_status', 'value' => (array) $agent_status ];
        }

        if($followup_status = $request->input('filters.followup_status', null)) {
            $criteria['whereIn'][] = [ 'field' => 'followup_status', 'value' => (array) $followup_status ];
        }

        if($delivery_status = $request->input('filters.delivery_status', null)) {
            $criteria['whereIn'][] = [ 'field' => 'delivery_status', 'value' => (array) $delivery_status ];
        }
        

        if($agent_id = $request->input('filters.agent_id', null)) {
            $criteria['whereIn'][] = [ 'field' => 'agent_id', 'value' => (array) $agent_id ];
        }
        
        if($product_id = $request->input('filters.product_id', null)) {
            $criteria['whereRelation'][] = [ 'relationship' => 'items',  'field' => 'product_id', 'value' => (array) $product_id ];
        }

        if($delivery_id = $request->input('filters.delivery_id', null)) {

            foreach($delivery_id as $value) {
                if($value == 0) {
                    $criteria['orWhereNull'][] = [ 'field' => 'delivery_id' ];
                }  else {
                    $criteria['orWhere'][] = [ 'field' => 'delivery_id', 'operator' => '=', 'value' => $value ];
                }
            }
        }


        if($is_followup = $request->input('is_followup', false)) {
            if($is_followup == 'true') {
                $criteria['where'][] = [ 'field' => 'agent_status', 'value' => OrderConfirmationEnum::CONFIRMED->value ];
                $criteria['where'][] = [ 'field' => 'delivery_id', 'operator' => '!=', 'value' => null ];
            }
        }

        if($treated_at = $request->input('filters.treated_at', [])) {
                $treated_at = self::getDateFromParams($request->input('filters', []), 'treated_at');

                $criteria['callbacks'][] = function (&$query) use($treated_at) {
                    $query->when($treated_at['from'], function ($query) use ($treated_at) {

                        $query->whereIn('orders.id', function ($subQuery) use ($treated_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '>=', $treated_at['from'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where(function ($q) {
                                    $q->where('fields', 'like', '%agent_status%')
                                    ->orWhere('fields', 'like', '%calls%');
                                });
                        });
                    })
                    ->when($treated_at['to'], function ($query) use ($treated_at) {
        
                        $query->whereIn('orders.id', function ($subQuery) use ($treated_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '<=', $treated_at['to'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where(function ($q) {
                                    $q->where('fields', 'like', '%agent_status%')
                                    ->orWhere('fields', 'like', '%calls%');
                                });
                        });
                    });
                };
        }

        
        if($dropped_at = $request->input('filters.dropped_at', [])) {
                $dropped_at = self::getDateFromParams($request->input('filters', []), 'dropped_at');

                $criteria['callbacks'][] = function (&$query) use($dropped_at) {
                    $query->when($dropped_at['from'], function ($query) use ($dropped_at) {

                        $query->whereIn('orders.id', function ($subQuery) use ($dropped_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '>=', $dropped_at['from'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where('fields', 'like', '%agent_id%');
                        });
                    })
                    ->when($dropped_at['to'], function ($query) use ($dropped_at) {
        
                        $query->whereIn('orders.id', function ($subQuery) use ($dropped_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '<=', $dropped_at['to'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where('fields', 'like', '%agent_id%');
                        });
                    });
                };
        }


        if($delivered_at = $request->input('filters.delivered_at', [])) {
                $delivered_at = self::getDateFromParams($request->input('filters', []), 'delivered_at');

                $criteria['callbacks'][] = function (&$query) use($delivered_at) {
                    $query->when($delivered_at['from'], function ($query) use ($delivered_at) {

                        $query->whereIn('orders.id', function ($subQuery) use ($delivered_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '>=', $delivered_at['from'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where('fields', 'like', '%new_value":"delivered"%');
                        });
                    })
                    ->when($delivered_at['to'], function ($query) use ($delivered_at) {
        
                        $query->whereIn('orders.id', function ($subQuery) use ($delivered_at) {
                            $subQuery->select('trackable_id')
                                ->from('history')
                                ->whereDate('created_at', '<=', $delivered_at['to'])
                                ->where('trackable_type', 'App\\Models\\Order')
                                ->where('fields', 'like', '%new_value":"delivered"%');
                        });
                    });
                };
        }

        self::sort($request, $criteria);
        
        return $criteria;
    }


    public static function getDateFromParams($params, $key)
    {
        $dateFrom = data_get($params, "$key.from");
        $dateTo = data_get($params, "$key.to");

        return [
            'from' => $dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : null,
            'to' => $dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : null,
        ];
    }

    public static function sort(Request $request, &$criteria) {
        $criteria['callbacks'][] = function (&$query) use ($request) {
            foreach($request->input('sorting', []) as $sorting) {
                $query->orderBy($sorting['field'], $sorting['direction']);
            }
        };
    }

}