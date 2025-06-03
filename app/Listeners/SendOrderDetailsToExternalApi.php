<?php

namespace App\Listeners;

use App\Models\Order;
use App\Events\OrderUpdated;
use App\Services\NawrisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderDetailsToExternalApi
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderUpdated &$event)
    {
        $order = $event->order;

        // Fetch the latest order with items
        $order->load('items');

        // Check order old attributes and new attributes
        $old_attributes = $order->getOriginal();
        $new_attributes = $order->getAttributes();

        try {
            if ((data_get($new_attributes, 'delivery_id') != data_get($old_attributes, 'delivery_id')) || $order->wasRecentlyCreated) {
                if (data_get($new_attributes, 'delivery_id') == NawrisService::id()) {
                    $this->handleInsert($order);
                } else if (data_get($old_attributes, 'delivery_id') == NawrisService::id() && data_get($new_attributes, 'delivery_id') != NawrisService::id()) {
                    $this->handleDelete($order);
                }
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }


    public function handleInsert(Order &$order)
    {
        // throw new \Exception('Error from nawris x', 500);
        $response = NawrisService::insert($order);


        // write response in laravel Log class
        // Log::info($response->json());
        Log::channel('api_calls')->info('API call made', [
            'request' => ['order_id' => $order->id],
            'event' => 'create',
            'response' => $response->json(),
        ]);




        if ($response->successful()) {
            $data = $response->json();
            if (data_get($data, 'success', null) == 1) {
                $order->nawris_code = data_get($data, 'result.code', null);
            } else {
                $order->nawris_code = null;
                $order->delivery_id = null;

                throw new \Exception(data_get($data, 'error_msg', null), 500);
            }
        } else {
            $data = $response->json();
            $errors = data_get($data, 'errors', []);
            $error_message = "";
            Log::info($errors);
            if (is_array($errors) && count($errors)) {
                foreach ($errors[0] as $key => $value) {
                    $error_message .= $value[0] . ' | ';
                }

                $error_message = rtrim($error_message, ' | ');
            } else {
                $error_message = "Order update failed";
            }
            throw new \Exception($error_message, 500);
        }
    }

    public function handleDelete(Order &$order)
    {
        $response = NawrisService::delete($order->nawris_code);
        // Log::info($response->json());

        Log::channel('api_calls')->info('API call made', [
            'request' => ['order_id' => $order->id, 'nawris_code' => $order->nawris_code],
            'event' => 'delete',
            'response' => $response->json(),
        ]);

        // throw new \Exception('Error from nawris x', 500);
        if ($response->successful()) {
            $data = $response->json();
            if (data_get($data, 'success', null) == 1) {
                $order->nawris_code = null;
            } else {
                if (data_get($data, 'error_msg', null) == 'order not found') {
                    $order->nawris_code = null;
                } else {
                    throw new \Exception('Order update failed: ' . data_get($data, 'message', null), 500);
                }
            }
        }
    }
}
