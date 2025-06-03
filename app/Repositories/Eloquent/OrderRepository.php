<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Events\OrderUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }


    public function create(array $data, array $items = [])
    {

        try {
            DB::beginTransaction();

            $order = parent::create($data);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => data_get($item, 'product_id'),
                    'product_variant_id' => data_get($item, 'product_variant_id'),
                    'price' => data_get($item, 'price', 0),
                    'quantity' => (int) data_get($item, 'quantity', 0),
                ]);
            }

            $order->save();
            event(new OrderUpdated($order));


            DB::commit();

            return $order;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getTraceAsString());
            throw new \Exception( $e->getMessage(), $e->getCode(), $e);
            // throw new \Exception('Order creation failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function insertMany(array $orders)
    {
        $result = [];
        foreach ($orders as $o) {
            $result[] = $this->create($o, $o['items']);
        }

        return $result;
    }

    public function update($id, array $data)
{
    try {
        DB::beginTransaction();

        $old_items = OrderItem::where('order_id', $id)->get();
        $new_items = data_get($data, 'items', []);

        // Create arrays to hold item IDs
        $old_item_ids = $old_items->pluck('id')->toArray();
        $new_item_ids = collect($new_items)->pluck('id')->toArray();

        // Detect deleted items
        $deleted_item_ids = array_diff($old_item_ids, $new_item_ids);

        // Delete the removed items
        foreach ($deleted_item_ids as $deleted_item_id) {
            OrderItem::where([
                'order_id' => $id,
                'id' => $deleted_item_id
            ])->first()?->delete();
        }

        // Loop through the new items
        foreach ($new_items as $item) {
            if (isset($item['is_new']) && $item['is_new'] === true) {
                // Handle new items
                OrderItem::create([
                    ...$item,
                    'order_id' => $id
                ]);
            } else {
                // Update existing items
                OrderItem::where([
                    'order_id' => $id,
                    'id' => $item['id']
                ])->first()?->update([
                    'product_variant_id' => data_get($item, 'product_variant_id'),
                    'product_id' => data_get($item, 'product_id'),
                    'price' => (float) data_get($item, 'price'),
                    'quantity' => (int) data_get($item, 'quantity'),
                ]);
            }
        }

        // Update the order
        $order = parent::update($id, $data);

        // Reset the items relationship to reflect the changes
        $order->unsetRelation('items');
        $order->load('items');

        // Commit the transaction
        // throw new \Exception('Order updating failed: xx');
        DB::commit();

        return $order;
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e->getTraceAsString());
        throw new \Exception($e->getMessage(), $e->getCode(), $e);
    }
}



    public function paginate($perPage = 10, array $options = [])
    {
        return $this->model
            ->orderBy(data_get($options, 'order_by', 'id'), data_get($options, 'order_direction', 'desc'))
            ->paginate($perPage);
    }


    public function getOrderStatusForAgent($agentId) {
        $result = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', 'oi.order_id')
            ->where('o.agent_id', $agentId)
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


    public function getProductsConfirmationByAgent($agentId) {
    
        $result = DB::table('order_items as oi')
        ->join('orders as o', 'o.id', 'oi.order_id')
        ->join('products as p', 'p.id', 'oi.product_id')
        ->where('o.agent_id', $agentId)
        ->select(
                'p.name',
                'p.id',
                'o.agent_status',
                DB::raw('COUNT(DISTINCT oi.order_id) as total_orders')
        )
        ->groupBy('p.name', 'p.id', 'o.agent_status')
        ->get();

        return $result;
    }
}
