<?php

namespace App\Repositories\Eloquent;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductCross;
use App\Models\ProductOffer;
use App\Models\ProductVariant;
use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\OrderConfirmationEnum;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }


    public function create(array $data, array $variants = [])
    {
        $product = parent::create($data);
        // throw new \Exception(json_encode($data));

        // if($product->has_variants) {
        $product->variants()->saveMany(
            array_map(fn($v) => new ProductVariant($v), $variants)
        );

        Log::info(json_encode($data));

        if (isset($data['cross_products']) && $data['cross_products']) {
            foreach ($data['cross_products'] as $cross_product) {
                ProductCross::create([
                    'product_id' => $product->id,
                    'cross_product_id' => $cross_product['cross_product_id'],
                    'created_by' => auth()->id(),
                    'price' => $cross_product['price'],
                    'note' => $cross_product['note'] ?? null,
                ]);
            }
        }

        if (isset($data['offers']) && $data['offers']) {
            foreach ($data['offers'] as $offer) {
                ProductOffer::create([
                    'quantity' => $offer['quantity'],
                    'product_id' => $product->id,
                    'created_by' => auth()->id(),
                    'price' => $offer['price'],
                    'note' => $offer['note'],
                ]);
            }
        }

        return $product;
        // }
    }

    public function getOrderCountForProduct($productId)
    {
        return DB::table('order_items')
            ->whereNull('order_items.deleted_at')
            ->where('product_id', $productId)
            ->distinct('order_id')
            ->count('order_id');
    }

    public function getOrderConfirmationForProduct($productId)
    {
        // Step 1: Fetch orders grouped by agent_status
        $orderStatusCounts = DB::table('orders')
            ->whereIn('orders.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->select('agent_status', DB::raw('count(*) as orders'))
            ->groupBy('agent_status')
            ->get();

        // Step 2: Calculate total orders and confirmed orders
        $totalOrders = 0;
        $confirmedOrders = 0;

        foreach ($orderStatusCounts as $status) {
            // Exclude duplications
            if ($status->agent_status == OrderConfirmationEnum::DUBPLICATE->value)
                continue;

            $totalOrders += $status->orders;

            // Assuming 'confirmed' is the status for confirmed orders
            if ($status->agent_status === OrderConfirmationEnum::CONFIRMED->value) {
                $confirmedOrders = $status->orders;
            }
        }

        // Step 3: Calculate confirmation rate
        $confirmationRate = $totalOrders > 0 ? ($confirmedOrders / $totalOrders) * 100 : 0;

        return round($confirmationRate, 2);
    }

    public function getOrderDeliveryForProduct($productId)
    {
        // Step 1: Fetch orders grouped by delivery_status
        $orderStatusCounts = DB::table('orders')
            ->whereIn('orders.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->where('agent_status', OrderConfirmationEnum::CONFIRMED->value)
            ->select('delivery_status', DB::raw('count(*) as orders'))
            ->groupBy('delivery_status')
            ->get();

        // Step 2: Calculate total orders and delivered orders
        $totalOrders = 0;
        $deliveredOrders = 0;

        foreach ($orderStatusCounts as $status) {

            $totalOrders += $status->orders;

            // Assuming 'delivered' is the status for delivered orders
            if ($status->delivery_status == OrderDeliveryEnum::DELIVERED->value || $status->delivery_status == OrderDeliveryEnum::SETTLED->value) {
                $deliveredOrders = $status->orders;
            }
        }

        // Step 3: Calculate confirmation rate
        $deliveryRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;

        return round($deliveryRate, 2);
    }

    public function getTotalQuantityForProduct($productId)
    {
        $product = $this->model->find($productId);

        if ($product->has_variants) {
            return $product->variants->sum('quantity');
        }

        return $product->quantity;
    }

    public function getAvailableQuantityForProduct($productId)
    {
        $quantity = $this->getTotalQuantityForProduct($productId);

        $deliveredQuantity = $this->getDeliveredQuantityForProduct($productId);
        $shippedQuantity = $this->getShippedQuantityForProduct($productId);

        return $quantity - $deliveredQuantity - $shippedQuantity;
    }

    public function getDeliveredQuantityForProduct($productId)
    {
        $results = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.deleted_at')
            ->whereIn('orders.delivery_status', [OrderDeliveryEnum::DELIVERED->value, OrderDeliveryEnum::SETTLED->value])
            ->where('product_id', $productId)
            ->select(DB::raw('sum(quantity) as quantity'))->first();

        return (int) $results->quantity ?? 0;
    }

    public function getShippedQuantityForProduct($productId)
    {
        $results = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn(
                'orders.delivery_status',
                [
                    OrderDeliveryEnum::DISPATCHED->value,
                    OrderDeliveryEnum::SENT_FOR_DELIVERY->value,
                    OrderDeliveryEnum::WITH_COURIER->value,
                    OrderDeliveryEnum::TO_COURIER->value,
                    OrderDeliveryEnum::POSTPONED_WITH_COURIER->value,
                    OrderDeliveryEnum::ON_WAY_TO_BRANCH->value,
                    OrderDeliveryEnum::IN_BRANCH->value,
                    OrderDeliveryEnum::RETURN_TO_BRANCH->value,
                    OrderDeliveryEnum::RETURN_WITH_COURIER->value,
                    OrderDeliveryEnum::RESERVED_BEFORE_DELIVERY->value,
                ]
            )
            ->where('product_id', $productId)
            ->whereNull('order_items.deleted_at')
            ->select(DB::raw('sum(quantity) as quantity'))->first();

        return (int) $results->quantity ?? 0;
    }

    public function getAgentsForProduct($productId)
    {

        $orders = DB::table('orders as o')
            ->select([
                'u.name',
                'u.id',
                'o.agent_status',
                'o.delivery_status',
                DB::raw('COUNT(*) as count')
            ])
            ->join('users as u', 'u.id', '=', 'o.agent_id')
            ->whereIn('o.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->groupBy('u.name', 'u.id', 'o.agent_status', 'o.delivery_status')
            ->get();

        $agents = [];

        // Process orders data
        foreach ($orders as $order) {
            $name = $order->name;

            // Initialize agent data if not already set
            if (!isset($agents[$name])) {
                $agents[$name] = [
                    'id' => $order->id,
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'delivered_orders' => 0,
                    'canceled_orders' => 0,
                    'reported_orders' => 0,
                    'no_answer_orders' => 0,
                    'duplicated_orders' => 0,
                    'settled_orders' => 0,
                ];
            }

            // Update total orders
            $agents[$name]['total_orders'] += $order->agent_status != OrderConfirmationEnum::DUBPLICATE ? $order->count : 0;

            // Update confirmed and delivered orders
            if ($order->agent_status === OrderConfirmationEnum::CONFIRMED->value) {
                $agents[$name]['confirmed_orders'] += $order->count;

                if ($order->delivery_status === OrderDeliveryEnum::DELIVERED->value) {
                    $agents[$name]['delivered_orders'] += $order->count;
                }
                if ($order->delivery_status === OrderDeliveryEnum::SETTLED->value) {
                    $agents[$name]['settled_orders'] += $order->count;
                }
            }

            if ($order->agent_status === OrderConfirmationEnum::CANCELED->value) {
                $agents[$name]['canceled_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::NO_ANSWER->value) {
                $agents[$name]['no_answer_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::REPORTED->value) {
                $agents[$name]['reported_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::DUBPLICATE->value) {
                $agents[$name]['duplicated_orders'] += $order->count;
            }
        }

        // Calculate rates and prepare the result
        $result = [];
        foreach ($agents as $name => $stats) {
            $confirmationRate = $stats['total_orders'] > 0
                ? round(($stats['confirmed_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $cancelRate = $stats['total_orders'] > 0
                ? round(($stats['canceled_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $reportingRate = $stats['total_orders'] > 0
                ? round(($stats['reported_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $noAnswerRate = $stats['total_orders'] > 0
                ? round(($stats['no_answer_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $duplicationRate = $stats['total_orders'] > 0
                ? round(($stats['duplicated_orders'] / ($stats['total_orders'] + $stats['duplicated_orders'])) * 100, 2)
                : 0;

            $deliveryRate = $stats['confirmed_orders'] > 0
                ? round(($stats['delivered_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $settledRate = $stats['confirmed_orders'] > 0
                ? round(($stats['settled_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $result[] = [
                'name' => $name,
                'id' => $stats['id'],
                'total_orders' => $stats['total_orders'] ?? 0,
                'confirmation_rate' => $confirmationRate,
                'confirmed_orders' => $stats['confirmed_orders'] ?? 0,
                'delivery_rate' => $deliveryRate,
                'delivered_orders' => $stats['delivered_orders'] ?? 0,
                'settled_rate' => $settledRate,
                'settled_orders' => $stats['settled_orders'] ?? 0,
                'cancelation_rate' => $cancelRate,
                'canceled_orders' => $stats['canceled_orders'] ?? 0,
                'reporting_rate' => $reportingRate,
                'reported_orders' => $stats['reported_orders'] ?? 0,
                'no_answer_rate' => $noAnswerRate,
                'no_answer_orders' => $stats['no_answer_orders'] ?? 0,
                'duplication_rate' => $duplicationRate,
                'duplicated_orders' => $stats['duplicated_orders'] ?? 0,

            ];
        }

        return $result;
    }

    public function getAgentsForProductByRange($productId, $params = [])
    {
        
        $from = $params['from'] ?? null;
        $to = $params['to'] ?? null;

        if ($from) {
            $from = Carbon::parse($from)->format('Y-m-d');
        }
        
        if ($to) {
            $to = Carbon::parse($to)->format('Y-m-d');
        }


        $orders = DB::table('orders as o')
            ->select([
                'u.name',
                'u.id',
                'o.agent_status',
                'o.delivery_status',
                DB::raw('COUNT(*) as count')
            ])
            ->join('users as u', 'u.id', '=', 'o.agent_id')
            ->whereIn('o.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->when($from, function ($query) use ($from) {
                $query->where('o.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->where('o.created_at', '<=', $to);
            })
            ->groupBy('u.name', 'u.id', 'o.agent_status', 'o.delivery_status')
            ->get();

        $agents = [];

        // Process orders data
        foreach ($orders as $order) {
            $name = $order->name;

            // Initialize agent data if not already set
            if (!isset($agents[$name])) {
                $agents[$name] = [
                    'id' => $order->id,
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'delivered_orders' => 0,
                    'canceled_orders' => 0,
                    'reported_orders' => 0,
                    'no_answer_orders' => 0,
                    'duplicated_orders' => 0,
                    'settled_orders' => 0,
                ];
            }

            // Update total orders
            $agents[$name]['total_orders'] += $order->agent_status != OrderConfirmationEnum::DUBPLICATE ? $order->count : 0;

            // Update confirmed and delivered orders
            if ($order->agent_status === OrderConfirmationEnum::CONFIRMED->value) {
                $agents[$name]['confirmed_orders'] += $order->count;

                if ($order->delivery_status === OrderDeliveryEnum::DELIVERED->value) {
                    $agents[$name]['delivered_orders'] += $order->count;
                }
                if ($order->delivery_status === OrderDeliveryEnum::SETTLED->value) {
                    $agents[$name]['settled_orders'] += $order->count;
                }
            }

            if ($order->agent_status === OrderConfirmationEnum::CANCELED->value) {
                $agents[$name]['canceled_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::NO_ANSWER->value) {
                $agents[$name]['no_answer_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::REPORTED->value) {
                $agents[$name]['reported_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::DUBPLICATE->value) {
                $agents[$name]['duplicated_orders'] += $order->count;
            }
        }

        // Calculate rates and prepare the result
        $result = [];
        foreach ($agents as $name => $stats) {
            $confirmationRate = $stats['total_orders'] > 0
                ? round(($stats['confirmed_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $cancelRate = $stats['total_orders'] > 0
                ? round(($stats['canceled_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $reportingRate = $stats['total_orders'] > 0
                ? round(($stats['reported_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $noAnswerRate = $stats['total_orders'] > 0
                ? round(($stats['no_answer_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $duplicationRate = $stats['total_orders'] > 0
                ? round(($stats['duplicated_orders'] / ($stats['total_orders'] + $stats['duplicated_orders'])) * 100, 2)
                : 0;

            $deliveryRate = $stats['confirmed_orders'] > 0
                ? round(($stats['delivered_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $settledRate = $stats['confirmed_orders'] > 0
                ? round(($stats['settled_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $result[] = [
                'name' => $name,
                'id' => $stats['id'],
                'total_orders' => $stats['total_orders'] ?? 0,
                'confirmation_rate' => $confirmationRate,
                'confirmed_orders' => $stats['confirmed_orders'] ?? 0,
                'delivery_rate' => $deliveryRate,
                'delivered_orders' => $stats['delivered_orders'] ?? 0,
                'settled_rate' => $settledRate,
                'settled_orders' => $stats['settled_orders'] ?? 0,
                'cancelation_rate' => $cancelRate,
                'canceled_orders' => $stats['canceled_orders'] ?? 0,
                'reporting_rate' => $reportingRate,
                'reported_orders' => $stats['reported_orders'] ?? 0,
                'no_answer_rate' => $noAnswerRate,
                'no_answer_orders' => $stats['no_answer_orders'] ?? 0,
                'duplication_rate' => $duplicationRate,
                'duplicated_orders' => $stats['duplicated_orders'] ?? 0,

            ];
        }

        return $result;
    }


    public function getMarketersForProduct($productId)
    {

        $orders = DB::table('orders as o')
            ->select([
                'u.name',
                'u.id',
                'o.agent_status',
                'o.delivery_status',
                DB::raw('COUNT(*) as count')
            ])
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('users as u', 'u.id', '=', 'gs.marketer_id')
            ->whereIn('o.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->groupBy('u.name', 'u.id', 'o.agent_status', 'o.delivery_status')
            ->get();

        $marketers = [];

        // Process orders data
        foreach ($orders as $order) {
            $name = $order->name;

            // Initialize marketer data if not already set
            if (!isset($marketers[$name])) {
                $marketers[$name] = [
                    'id' => $order->id,
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'delivered_orders' => 0,
                    'canceled_orders' => 0,
                    'reported_orders' => 0,
                    'no_answer_orders' => 0,
                    'duplicated_orders' => 0,
                    'settled_orders' => 0,

                ];
            }

            // Update total orders
            $marketers[$name]['total_orders'] += $order->agent_status != OrderConfirmationEnum::DUBPLICATE ? $order->count : 0;

            // Update confirmed and delivered orders
            if ($order->agent_status === OrderConfirmationEnum::CONFIRMED->value) {
                $marketers[$name]['confirmed_orders'] += $order->count;

                if ($order->delivery_status === OrderDeliveryEnum::DELIVERED->value) {
                    $marketers[$name]['delivered_orders'] += $order->count;
                }
                if ($order->delivery_status === OrderDeliveryEnum::SETTLED->value) {
                    $marketers[$name]['settled_orders'] += $order->count;
                }
            }

            if ($order->agent_status === OrderConfirmationEnum::CANCELED->value) {
                $marketers[$name]['canceled_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::NO_ANSWER->value) {
                $marketers[$name]['no_answer_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::REPORTED->value) {
                $marketers[$name]['reported_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::DUBPLICATE->value) {
                $marketers[$name]['duplicated_orders'] += $order->count;
            }
        }

        // Calculate rates and prepare the result
        $result = [];
        foreach ($marketers as $name => $stats) {
            $confirmationRate = $stats['total_orders'] > 0
                ? round(($stats['confirmed_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $cancelRate = $stats['total_orders'] > 0
                ? round(($stats['canceled_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $reportingRate = $stats['total_orders'] > 0
                ? round(($stats['reported_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $noAnswerRate = $stats['total_orders'] > 0
                ? round(($stats['no_answer_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $duplicationRate = $stats['total_orders'] > 0
                ? round(($stats['duplicated_orders'] / ($stats['total_orders'] + $stats['duplicated_orders'])) * 100, 2)
                : 0;

            $deliveryRate = $stats['confirmed_orders'] > 0
                ? round(($stats['delivered_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $settledRate = $stats['confirmed_orders'] > 0
                ? round(($stats['settled_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $result[] = [
                'name' => $name,
                'id' => $stats['id'],
                'total_orders' => $stats['total_orders'] ?? 0,
                'confirmation_rate' => $confirmationRate,
                'confirmed_orders' => $stats['confirmed_orders'] ?? 0,
                'delivery_rate' => $deliveryRate,
                'delivered_orders' => $stats['delivered_orders'] ?? 0,
                'settled_rate' => $settledRate,
                'settled_orders' => $stats['settled_orders'] ?? 0,
                'cancelation_rate' => $cancelRate,
                'canceled_orders' => $stats['canceled_orders'] ?? 0,
                'reporting_rate' => $reportingRate,
                'reported_orders' => $stats['reported_orders'] ?? 0,
                'no_answer_rate' => $noAnswerRate,
                'no_answer_orders' => $stats['no_answer_orders'] ?? 0,
                'duplication_rate' => $duplicationRate,
                'duplicated_orders' => $stats['duplicated_orders'] ?? 0,

            ];
        }

        return $result;
    }

    public function getMarketersForProductByRange($productId, $params = [])
    {

        // from: 2025-04-30T23:00:00.000Z
        // to: 2025-05-02T23:00:00.000Z

        // check if exists and parse it examples above to date as 2025-04-30;
        $from = $params['from'] ?? null;
        $to = $params['to'] ?? null;

        if ($from) {
            $from = Carbon::parse($from)->format('Y-m-d');
        }
        
        if ($to) {
            $to = Carbon::parse($to)->format('Y-m-d');
        }


        $orders = DB::table('orders as o')
            ->select([
                'u.name',
                'u.id',
                'o.agent_status',
                'o.delivery_status',
                DB::raw('COUNT(*) as count')
            ])
            ->join('google_sheets as gs', 'o.google_sheet_id', '=', 'gs.id')
            ->join('users as u', 'u.id', '=', 'gs.marketer_id')
            ->whereIn('o.id', function ($query) use ($productId) {
                $query->select('oi.order_id')
                    ->from('order_items as oi')
                    ->whereNull('oi.deleted_at')
                    ->where('oi.product_id', $productId);
            })
            ->when($from, function ($query) use ($from) {
                $query->where('o.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->where('o.created_at', '<=', $to);
            })
            ->groupBy('u.name', 'u.id', 'o.agent_status', 'o.delivery_status')
            ->get();

        $marketers = [];

        // Process orders data
        foreach ($orders as $order) {
            $name = $order->name;

            // Initialize marketer data if not already set
            if (!isset($marketers[$name])) {
                $marketers[$name] = [
                    'id' => $order->id,
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'delivered_orders' => 0,
                    'canceled_orders' => 0,
                    'reported_orders' => 0,
                    'no_answer_orders' => 0,
                    'duplicated_orders' => 0,
                    'settled_orders' => 0,

                ];
            }

            // Update total orders
            $marketers[$name]['total_orders'] += $order->agent_status != OrderConfirmationEnum::DUBPLICATE ? $order->count : 0;

            // Update confirmed and delivered orders
            if ($order->agent_status === OrderConfirmationEnum::CONFIRMED->value) {
                $marketers[$name]['confirmed_orders'] += $order->count;

                if ($order->delivery_status === OrderDeliveryEnum::DELIVERED->value) {
                    $marketers[$name]['delivered_orders'] += $order->count;
                }
                if ($order->delivery_status === OrderDeliveryEnum::SETTLED->value) {
                    $marketers[$name]['settled_orders'] += $order->count;
                }
            }

            if ($order->agent_status === OrderConfirmationEnum::CANCELED->value) {
                $marketers[$name]['canceled_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::NO_ANSWER->value) {
                $marketers[$name]['no_answer_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::REPORTED->value) {
                $marketers[$name]['reported_orders'] += $order->count;
            }

            if ($order->agent_status === OrderConfirmationEnum::DUBPLICATE->value) {
                $marketers[$name]['duplicated_orders'] += $order->count;
            }
        }

        // Calculate rates and prepare the result
        $result = [];
        foreach ($marketers as $name => $stats) {
            $confirmationRate = $stats['total_orders'] > 0
                ? round(($stats['confirmed_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $cancelRate = $stats['total_orders'] > 0
                ? round(($stats['canceled_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $reportingRate = $stats['total_orders'] > 0
                ? round(($stats['reported_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $noAnswerRate = $stats['total_orders'] > 0
                ? round(($stats['no_answer_orders'] / $stats['total_orders']) * 100, 2)
                : 0;

            $duplicationRate = $stats['total_orders'] > 0
                ? round(($stats['duplicated_orders'] / ($stats['total_orders'] + $stats['duplicated_orders'])) * 100, 2)
                : 0;

            $deliveryRate = $stats['confirmed_orders'] > 0
                ? round(($stats['delivered_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $settledRate = $stats['confirmed_orders'] > 0
                ? round(($stats['settled_orders'] / $stats['confirmed_orders']) * 100, 2)
                : 0;

            $result[] = [
                'name' => $name,
                'id' => $stats['id'],
                'total_orders' => $stats['total_orders'] ?? 0,
                'confirmation_rate' => $confirmationRate,
                'confirmed_orders' => $stats['confirmed_orders'] ?? 0,
                'delivery_rate' => $deliveryRate,
                'delivered_orders' => $stats['delivered_orders'] ?? 0,
                'settled_rate' => $settledRate,
                'settled_orders' => $stats['settled_orders'] ?? 0,
                'cancelation_rate' => $cancelRate,
                'canceled_orders' => $stats['canceled_orders'] ?? 0,
                'reporting_rate' => $reportingRate,
                'reported_orders' => $stats['reported_orders'] ?? 0,
                'no_answer_rate' => $noAnswerRate,
                'no_answer_orders' => $stats['no_answer_orders'] ?? 0,
                'duplication_rate' => $duplicationRate,
                'duplicated_orders' => $stats['duplicated_orders'] ?? 0,

            ];
        }

        return $result;
    }

    public function getMarketersWithAdsForProduct($productId)
    {
        return [];
    }

    public function getAdSpendForProduct($productId)
    {
        $spend = DB::table('ads')
            ->whereNull('deleted_at')
            ->where('product_id', $productId)->sum('spend');

        return $spend;
    }


    public function getStatusForProduct($productId)
    {

        $result = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', 'oi.order_id')
            ->where('oi.product_id', $productId)
            ->whereNull('oi.deleted_at')
            ->whereNull('o.deleted_at')
            ->select(
                'o.agent_status',
                'o.delivery_status',
                DB::raw('SUM(oi.price) as total'),
                DB::raw('COUNT(DISTINCT o.id) as total_orders'),
            )
            ->groupBy('o.agent_status', 'o.delivery_status')
            ->get();

        return $result;
    }


    public function update($id, array $data, array $variants = [], $skipVariantUpdate = false)
    {
        // Update the product
        $product = parent::update($id, $data);
        // $product = parent::find($id);

        if (!$skipVariantUpdate) {
            // Collect current variant IDs from the database
            $currentVariantIds = $product->variants->pluck('id')->toArray();

            // Collect new variant IDs from the incoming data
            $newVariantIds = array_filter(array_column($variants, 'id'));

            // Delete variants that are no longer in the incoming data
            $variantsToDelete = array_diff($currentVariantIds, $newVariantIds);
            $product->variants()->whereIn('id', $variantsToDelete)->delete();

            // Update or create variants
            foreach ($variants as $variantData) {
                $product->variants()->updateOrCreate(
                    ['id' => $variantData['id'] ?? null, 'product_id' => $id],
                    $variantData
                );
            }
        }

        Log::info(json_encode($data));

        // Handle cross products
        if (isset($data['cross_products']) && is_array($data['cross_products'])) {
            // Get current cross product IDs for this product
            $currentCrossProductIds = DB::table('product_crosses')
                ->where('product_id', $id)
                ->pluck('id')
                ->toArray();

            // Track cross product IDs being processed
            $processedCrossProductIds = [];

            foreach ($data['cross_products'] as $crossProductData) {
                // If created flag is true or no ID exists, create new record
                if ((isset($crossProductData['created']) && $crossProductData['created']) || !isset($crossProductData['id'])) {
                    ProductCross::create([
                        'product_id' => $id,
                        'cross_product_id' => $crossProductData['cross_product_id'],
                        'created_by' => auth()->id(),
                        'price' => $crossProductData['price'],
                        'note' => $crossProductData['note'] ?? null,
                    ]);
                }
                // If updated flag is true and ID exists, update record
                elseif (isset($crossProductData['updated']) && $crossProductData['updated'] && isset($crossProductData['id'])) {
                    ProductCross::where('id', $crossProductData['id'])
                        ->where('product_id', $id)
                        ->update([
                            'cross_product_id' => $crossProductData['cross_product_id'],
                            'price' => $crossProductData['price'],
                            'note' => $crossProductData['note'] ?? null,
                        ]);

                    $processedCrossProductIds[] = $crossProductData['id'];
                }
                // If no created/updated flag but has ID, keep track of it
                elseif (isset($crossProductData['id'])) {
                    $processedCrossProductIds[] = $crossProductData['id'];
                }
            }

            // Delete cross products that are no longer in the incoming data
            $crossProductsToDelete = array_diff($currentCrossProductIds, $processedCrossProductIds);
            if (!empty($crossProductsToDelete)) {
                ProductCross::whereIn('id', $crossProductsToDelete)->delete();
            }
        }

        // Handle product offers
        if (isset($data['offers']) && is_array($data['offers'])) {
            // Get current offer IDs for this product
            $currentOfferIds = DB::table('product_offers')
                ->where('product_id', $id)
                ->pluck('id')
                ->toArray();

            // Track offer IDs being processed
            $processedOfferIds = [];

            foreach ($data['offers'] as $offerData) {
                // If created flag is true or no ID exists, create new record
                if ((isset($offerData['created']) && $offerData['created']) || !isset($offerData['id'])) {
                    ProductOffer::create([
                        'product_id' => $id,
                        'quantity' => $offerData['quantity'],
                        'created_by' => auth()->id(),
                        'price' => $offerData['price'],
                        'note' => $offerData['note'] ?? null,
                    ]);
                }
                // If updated flag is true and ID exists, update record
                elseif (isset($offerData['updated']) && $offerData['updated'] && isset($offerData['id'])) {
                    ProductOffer::where('id', $offerData['id'])
                        ->where('product_id', $id)
                        ->update([
                            'quantity' => $offerData['quantity'],
                            'price' => $offerData['price'],
                            'note' => $offerData['note'] ?? null,
                        ]);

                    $processedOfferIds[] = $offerData['id'];
                }
                // If no created/updated flag but has ID, keep track of it
                elseif (isset($offerData['id'])) {
                    $processedOfferIds[] = $offerData['id'];
                }
            }

            // Delete offers that are no longer in the incoming data
            $offersToDelete = array_diff($currentOfferIds, $processedOfferIds);
            if (!empty($offersToDelete)) {
                ProductOffer::whereIn('id', $offersToDelete)->delete();
            }
        }

        return $product;
    }

    public function topProducts($params = [])
    {

        $params = $_REQUEST;

        // Get exchange rate from request or use default
        $exchange_rate = $params['exchange'] ?? 4.88;

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

        if (!empty($date_range['from'])) {
            $orders_query->where('o.created_at', '>=', $date_range['from']);
        }
        if (!empty($date_range['to'])) {
            $orders_query->where('o.created_at', '<=', $date_range['to']);
        }

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
            ->leftJoin(DB::raw('(
                SELECT MIN(id) as id, trackable_id
                FROM history
                WHERE JSON_CONTAINS(fields, \'{"field": "delivery_status", "new_value": "delivered"}\')' .
                (!empty($date_range['from']) ? ' AND created_at >= "' . $date_range['from'] . '"' : '') .
                (!empty($date_range['to']) ? ' AND created_at <= "' . $date_range['to'] . '"' : '') .
                '
                GROUP BY trackable_id
            ) as h'), 'h.trackable_id', '=', 'o.id')
            ->whereIn('oi.product_id', $product_ids)
            ->whereNull('oi.deleted_at')
            ->whereNotNull('h.trackable_id');

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
            $product_map[$data->product_id]['total_delivered_quantity'] = (int) $data->quantity;
            $product_map[$data->product_id]['shipping_cost'] = (float) $data->shipping_cost;
            $product_map[$data->product_id]['total_delivered_product_cost'] = $data->product_cost;
        }

        // Get confirmed orders
        $confirmed_query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('history as h', function ($join) use ($date_range) {
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
        usort($data, function ($a, $b) use ($sortBy, $sortDirection) {
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
            'per_page' => (int) $perPage,
            'current_page' => (int) $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];


        // Return the data in JSON format
        return [
            'data' => $paginatedData,
            ...$pagination
        ];
    }


    public function topProductsOrigin($params = [])
    {
        $date_range = [
            'from' => data_get($params, 'from'),
            'to' => data_get($params, 'to')
        ];

        $search = data_get($params, 'search', null);

        // Get exchange rate from params or use default
        $exchange = data_get($params, 'exchange', 4.88);

        // Subquery to calculate total spend per product
        $adsSpendSubquery = DB::table('ads')
            ->select('product_id', DB::raw('SUM(spend) AS total_spend'))
            ->whereNull('deleted_at');

        if (!empty($date_range['from'])) {
            $adsSpendSubquery->where('spent_in', '>=', $date_range['from']);
        }
        if (!empty($date_range['to'])) {
            $adsSpendSubquery->where('spent_in', '<=', $date_range['to']);
        }

        $adsSpendSql = $adsSpendSubquery->groupBy('product_id')->toSql();

        // Build the base query with all joins and conditions
        $baseQuery = DB::table('products')
            ->leftJoin('order_items as oi', function ($join) use ($date_range) {
                $join->on('oi.product_id', '=', 'products.id')
                    ->whereNull('oi.deleted_at');

                if (!empty($date_range['from'])) {
                    $join->where('oi.created_at', '>=', $date_range['from']);
                }
                if (!empty($date_range['to'])) {
                    $join->where('oi.created_at', '<=', $date_range['to']);
                }
            })
            ->leftJoin('orders as o', 'oi.order_id', '=', 'o.id')
            ->leftJoin('cities as c', 'c.name', '=', 'o.customer_city')
            ->leftJoin('history as h', function ($join) {
                $join->on('h.trackable_id', '=', 'o.id')
                    ->where('h.trackable_type', 'App\\Models\\Order')
                    ->where(function ($query) {
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(h.fields, '$.new_value')) = 'delivered'")
                            ->orWhere('h.fields', 'LIKE', '%\"new_value\":\"delivered\"%');
                    });
            })
            ->leftJoin(DB::raw("($adsSpendSql) as ads_spend"), 'ads_spend.product_id', '=', 'products.id')

            ->whereNull('products.deleted_at');

        // Merge bindings from the adsSpendSubquery
        $baseQuery->mergeBindings($adsSpendSubquery);

        // Calculate total number of products
        $total = DB::table('products')->count();

        // Pagination parameters
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);

        // Define profit calculation for reuse
        $profitCalculation = "(SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN oi.price / {$exchange} ELSE 0 END) - 
                        (COALESCE(ads_spend.total_spend, 0) + 
                         SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN c.shipping_cost / {$exchange} ELSE 0 END) + 
                         SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN oi.quantity * products.buying_price ELSE 0 END) + 
                         COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN oi.order_id END)))";

        // Build the data query from the base query
        $dataQuery = clone $baseQuery;

        $dataQuery->select(
            'products.id',
            'products.name',
            DB::raw('COUNT(DISTINCT oi.order_id) AS total_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.delivery_status IN ("delivered", "settled") THEN o.id END) AS total_delivered_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status IN ("confirmed") THEN o.id END) AS total_confirmed_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status IN ("duplicate") THEN o.id END) AS total_duplicated_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN oi.order_id END) AS total_delivered_orders_in_range'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.price ELSE 0 END) AS total_turnover'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  c.shipping_cost ELSE 0 END) AS shipping_cost'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.quantity ELSE 0 END) AS total_delivered_quantity'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.quantity * products.buying_price ELSE 0 END) AS total_delivered_product_cost'),
            DB::raw('COALESCE(ads_spend.total_spend, 0) AS total_spend'),
            DB::raw("{$profitCalculation} AS total_profit"),
            DB::raw("CASE WHEN COUNT(DISTINCT CASE WHEN o.delivery_status IN ('delivered', 'settled') THEN o.id END) > 0 
                         THEN {$profitCalculation} / COUNT(DISTINCT CASE WHEN o.delivery_status IN ('delivered', 'settled') THEN o.id END) 
                         ELSE 0 END AS profit_per_order")
        )
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('products.name', 'LIKE', "%$search%")
                        ->orWhere('products.sku', 'LIKE', "%$search%");
                });
            })
            ->groupBy('products.id', 'products.name', 'ads_spend.total_spend');

        // Apply sorting - check if sort is by total_profit
        $sortBy = data_get($params, 'sorting.sortBy', 'total_orders');
        $sortDirection = data_get($params, 'sorting.sortDirection', 'desc');

        $dataQuery->orderBy($sortBy, $sortDirection)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage);

        // Execute the data query
        $results = $dataQuery->get();

        // Build pagination information
        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];

        // Return results with pagination
        return [
            'data' => $results,
            ...$pagination
        ];
    }

    public function getOffersForProduct($ids)
    {
        // use db and also return the product name with other fields
        $result = DB::table('product_offers as po')
            ->join('products as p', 'p.id', 'po.product_id')
            ->whereIn('po.product_id', $ids)
            ->select('po.*', 'p.name as product_name')
            ->get();

        return $result;
    }


    public function getCrossProductsForProduct($ids)
    {
        $result = DB::table('product_crosses as pc')
            ->join('products as p', 'p.id', 'pc.cross_product_id')
            ->whereIn('pc.product_id', $ids)
            ->select('pc.*', 'p.name as cross_product_name')
            ->get();

        return $result;
    }

    public function productAnalyticsOrigin($product_id, $params = [])
    {
        $date_range = [
            'from' => data_get($params, 'from'),
            'to' => data_get($params, 'to')
        ];

        $search = data_get($params, 'search', null);

        // Get exchange rate from params or use default
        $exchange = data_get($params, 'exchange', 4.88);

        // Subquery to calculate total spend per product
        $adsSpendSubquery = DB::table('ads')
            ->select('product_id', DB::raw('SUM(spend) AS total_spend'))
            ->whereNull('deleted_at');

        if (!empty($date_range['from'])) {
            $adsSpendSubquery->where('spent_in', '>=', $date_range['from']);
        }
        if (!empty($date_range['to'])) {
            $adsSpendSubquery->where('spent_in', '<=', $date_range['to']);
        }

        $adsSpendSql = $adsSpendSubquery->groupBy('product_id')->toSql();

        // Build the base query with all joins and conditions
        $baseQuery = DB::table('products')
            ->leftJoin('order_items as oi', function ($join) use ($date_range) {
                $join->on('oi.product_id', '=', 'products.id')
                    ->whereNull('oi.deleted_at');

                if (!empty($date_range['from'])) {
                    $join->where('oi.created_at', '>=', $date_range['from']);
                }
                if (!empty($date_range['to'])) {
                    $join->where('oi.created_at', '<=', $date_range['to']);
                }
            })
            ->leftJoin('orders as o', 'oi.order_id', '=', 'o.id')
            ->leftJoin('cities as c', 'c.name', '=', 'o.customer_city')
            ->leftJoin('history as h', function ($join) {
                $join->on('h.trackable_id', '=', 'o.id')
                    ->where('h.trackable_type', 'App\\Models\\Order')
                    ->where(function ($query) {
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(h.fields, '$.new_value')) = 'delivered'")
                            ->orWhere('h.fields', 'LIKE', '%\"new_value\":\"delivered\"%');
                    });
            })
            ->leftJoin(DB::raw("($adsSpendSql) as ads_spend"), 'ads_spend.product_id', '=', 'products.id')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('products.name', 'LIKE', "%$search%")
                        ->orWhere('products.sku', 'LIKE', "%$search%");
                });
            })
            //->where('products.id', $product_id)
            ->whereNull('products.deleted_at');

        // Merge bindings from the adsSpendSubquery
        $baseQuery->mergeBindings($adsSpendSubquery);

        // Calculate total number of products
        $total = DB::table('products')->count();

        // Pagination parameters
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);

        // Define profit calculation for reuse
        $profitCalculation = "(SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN oi.price / {$exchange} ELSE 0 END) - 
                        (COALESCE(ads_spend.total_spend, 0) + 
                         SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN c.shipping_cost / {$exchange} ELSE 0 END) + 
                         SUM(CASE WHEN o.agent_status = 'confirmed' AND o.delivery_status IN ('delivered', 'settled') THEN oi.quantity * products.buying_price ELSE 0 END) + 
                         COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN oi.order_id END)))";

        // Build the data query from the base query
        $dataQuery = clone $baseQuery;

        $dataQuery->select(
            'products.id',
            'products.name',
            DB::raw('COUNT(DISTINCT oi.order_id) AS total_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.delivery_status IN ("delivered", "settled") THEN o.id END) AS total_delivered_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status IN ("confirmed") THEN o.id END) AS total_confirmed_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN o.agent_status IN ("duplicate") THEN o.id END) AS total_duplicated_orders'),
            DB::raw('COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN oi.order_id END) AS total_delivered_orders_in_range'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.price ELSE 0 END) AS total_turnover'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  c.shipping_cost ELSE 0 END) AS shipping_cost'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.quantity ELSE 0 END) AS total_delivered_quantity'),
            DB::raw('SUM(CASE WHEN o.agent_status = "confirmed" AND o.delivery_status IN ("delivered", "settled") THEN  oi.quantity * products.buying_price ELSE 0 END) AS total_delivered_product_cost'),
            DB::raw('COALESCE(ads_spend.total_spend, 0) AS total_spend'),
            DB::raw("{$profitCalculation} AS total_profit"),
            DB::raw("CASE WHEN COUNT(DISTINCT CASE WHEN o.delivery_status IN ('delivered', 'settled') THEN o.id END) > 0 
                         THEN {$profitCalculation} / COUNT(DISTINCT CASE WHEN o.delivery_status IN ('delivered', 'settled') THEN o.id END) 
                         ELSE 0 END AS profit_per_order")
        )
            ->where('products.id', $product_id)
            ->groupBy('products.id', 'products.name', 'ads_spend.total_spend');

        // Apply sorting - check if sort is by total_profit
        $sortBy = data_get($params, 'sorting.sortBy', 'total_orders');
        $sortDirection = data_get($params, 'sorting.sortDirection', 'desc');

        $dataQuery->orderBy($sortBy, $sortDirection)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage);

        // Execute the data query
        $results = $dataQuery->first();
        return $results;

    }

    public function productAnalytics($params = []) {
        // Get exchange rate from request or use default
        $exchange_rate = data_get($params, 'exchange', 4.88);
        $product_id = data_get($params, 'product_id');

        // Date range filters
        $date_range = [
            'from' => data_get($params, 'from'),
            'to' => data_get($params, 'to')
        ];
        
        // Base product query
        $productQuery = DB::table('products')
            ->select('id', 'name', 'buying_price')
            ->where('id', $product_id)
            ->whereNull('deleted_at');

        // Get product
        $product = $productQuery->first();
        
        
        if (!$product) {
            return null;
        }

        // Initialize stats array
        $stats = [
            'id' => $product->id,
            'name' => $product->name,
            'total_orders' => 0,
            'total_delivered_orders' => 0, 
            'total_confirmed_orders' => 0,
            'total_duplicated_orders' => 0,
            'total_delivered_orders_in_range' => 0,
            'total_turnover' => 0,
            'shipping_cost' => 0,
            'total_delivered_quantity' => 0,
            'total_delivered_product_cost' => 0,
            'total_spend' => 0,
            'total_profit' => 0,
            'profit_per_order' => 0
        ];

        // COMBINED QUERY 1: Get orders, duplicated orders in a single query
        $orders_query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->where('oi.product_id', $product_id)
            ->whereNull('oi.deleted_at');

        if (!empty($date_range['from'])) {
            $orders_query->where('o.created_at', '>=', $date_range['from']);
        }
        if (!empty($date_range['to'])) {
            $orders_query->where('o.created_at', '<=', $date_range['to']);
        }

        $orders_data = $orders_query->select(
            DB::raw('COUNT(DISTINCT o.id) as total_orders'),
            DB::raw('SUM(CASE WHEN o.agent_status = "duplicate" THEN 1 ELSE 0 END) as duplicated_orders')
        )->first();

        $stats['total_orders'] = $orders_data->total_orders;
        $stats['total_duplicated_orders'] = $orders_data->duplicated_orders;

        // COMBINED QUERY 2: Get delivered orders, turnover, quantity, and shipping_cost in a single query
        $delivered_query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->leftJoin('cities as c', 'c.name', '=', 'o.customer_city')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->leftJoin(DB::raw('(
                SELECT MIN(id) as id, trackable_id
                FROM history
                WHERE JSON_CONTAINS(fields, \'{"field": "delivery_status", "new_value": "delivered"}\')' .
                (!empty($date_range['from']) ? ' AND created_at >= "' . $date_range['from'] . '"' : '') .
                (!empty($date_range['to']) ? ' AND created_at <= "' . $date_range['to'] . '"' : '') .
                '
                GROUP BY trackable_id
            ) as h'), 'h.trackable_id', '=', 'o.id')
            ->where('oi.product_id', $product_id)
            ->whereNull('oi.deleted_at')
            ->whereNotNull('h.trackable_id');

        $delivered_data = $delivered_query->select(
            DB::raw('COUNT(DISTINCT o.id) as delivered_count'),
            DB::raw('COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN o.id END) as delivered_in_range'),
            DB::raw('SUM(oi.price) as turnover'),
            DB::raw('SUM(oi.quantity) as quantity'),
            DB::raw('SUM(c.shipping_cost) as shipping_cost'),
            DB::raw('SUM(p.buying_price * oi.quantity) as product_cost')
        )->first();

        $stats['total_delivered_orders'] = $delivered_data->delivered_count;
        $stats['total_delivered_orders_in_range'] = $delivered_data->delivered_in_range;
        $stats['total_turnover'] = $delivered_data->turnover;
        $stats['total_delivered_quantity'] = (int)$delivered_data->quantity;
        $stats['shipping_cost'] = (float)$delivered_data->shipping_cost;
        $stats['total_delivered_product_cost'] = $delivered_data->product_cost;

        // Get confirmed orders
        $confirmed_query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('history as h', function ($join) use ($date_range) {
                $join->on('h.trackable_id', '=', 'o.id')
                    ->whereRaw('JSON_CONTAINS(h.fields, \'{"field": "agent_status", "new_value": "confirmed"}\')');

                if (!empty($date_range['from'])) {
                    $join->where('h.created_at', '>=', $date_range['from']);
                }
                if (!empty($date_range['to'])) {
                    $join->where('h.created_at', '<=', $date_range['to']);
                }
            })
            ->where('oi.product_id', $product_id);

        $stats['total_confirmed_orders'] = $confirmed_query->count(DB::raw('DISTINCT o.id'));

        // Get ads spend
        $ads_query = DB::table('ads as a')
            ->where('a.product_id', $product_id)
            ->whereNull('a.deleted_at');

        if (!empty($date_range['from'])) {
            $ads_query->where('a.spent_in', '>=', $date_range['from']);
        }
        if (!empty($date_range['to'])) {
            $ads_query->where('a.spent_in', '<=', $date_range['to']);
        }

        $stats['total_spend'] = round($ads_query->sum('spend'), 2);

        // Calculate profit
        $turnover_in_usd = $stats['total_turnover'] / $exchange_rate;
        $shipping_cost_in_usd = $stats['shipping_cost'] / $exchange_rate;
        $delivery_fee = $stats['total_delivered_orders'] * 1; // $1 per delivered order

        $stats['total_profit'] = round($turnover_in_usd - ($stats['total_spend'] + $shipping_cost_in_usd + $stats['total_delivered_product_cost'] + $delivery_fee), 2);
        $stats['profit_per_order'] = $stats['total_delivered_orders'] > 0 ? 
            round($stats['total_profit'] / $stats['total_delivered_orders'], 2) : 0;

        return (object)$stats;
    }

    

    /**
     * Get empty product analytics structure with zero values
     */
    private function getEmptyProductAnalytics($id = null, $name = null)
    {
        return [
            'id' => $id ?? 0,
            'name' => $name ?? '',
            'total_orders' => 0,
            'total_delivered_orders' => 0,
            'total_confirmed_orders' => 0,
            'total_duplicated_orders' => 0,
            'total_delivered_orders_in_range' => 0,
            'total_turnover' => 0,
            'shipping_cost' => 0,
            'total_delivered_quantity' => 0,
            'total_delivered_product_cost' => 0,
            'total_spend' => 0,
            'total_profit' => 0,
            'profit_per_order' => 0
        ];
    }

    public function getTotalDeliveredOrdersForProduct($productId)
    {
        $result = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.deleted_at')
            ->where('order_items.product_id', $productId)
            ->whereIn('orders.delivery_status', [
                OrderDeliveryEnum::DELIVERED->value,
                OrderDeliveryEnum::SETTLED->value
            ])
            ->distinct('orders.id')
            ->count('orders.id');

        return $result ?? 0;
    }

    public function getTotalConfirmedOrdersForProduct($productId)
    {
        $result = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.deleted_at')
            ->where('order_items.product_id', $productId)
            ->where('orders.agent_status', OrderConfirmationEnum::CONFIRMED->value)
            ->distinct('orders.id')
            ->count('orders.id');

        return $result ?? 0;
    }

    public function getTotalDuplicatedOrdersForProduct($productId)
    {
        $result = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.deleted_at')
            ->where('order_items.product_id', $productId)
            ->where('orders.agent_status', OrderConfirmationEnum::DUBPLICATE->value)
            ->distinct('orders.id')
            ->count('orders.id');

        return $result ?? 0;
    }

}
