<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use App\Enums\NawrisOrderTypeEnum;
use Illuminate\Support\Facades\Log;
use App\Enums\OrderConfirmationEnum;
use Illuminate\Support\Facades\Http;

class NawrisService
{

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function id()
    {
        return env('NAWRIS_USER_ID');
    }

    public static function insert($order)
    {
        $formated = self::format($order);

        return self::http('add-order', $formated);
    }

    public static function delete($code)
    {
        return self::http('delete-order', ['search_Key' => $code]);
    }

    public static function format($order)
    {

        switch ($order->agent_status) {
            case OrderConfirmationEnum::CHANGE->value:
                $type = NawrisOrderTypeEnum::CHANGE->value;
                break;
            case OrderConfirmationEnum::CONFIRMED->value:
                $type = NawrisOrderTypeEnum::NORMAL->value;
                break;
            case OrderConfirmationEnum::REFUND->value:
                $type = NawrisOrderTypeEnum::REFUND->value;
                break;

            default:
                $type = NawrisOrderTypeEnum::NORMAL->value;
                break;
        }


        return [
            'main_client_code' => env('NAWRIS_MAIN_CLIENT_CODE'),
            'second_client' => null,
            'receiver' => $order->customer_name,
            'phone1' => $order->customer_phone,
            'phone2' => null,
            'government' => $order->customer_city,
            'area' => $order->customer_area,
            'address' => $order->customer_address,
            'notes' => $order->agent_notes,
            'invoice_number' => null,
            'order_summary' => self::orderSummary($order),
            'amount_to_be_collected' => $order->amount,
            'return_amount' => null,
            'api_followup_phone' => null,
            'is_order' => $type, // 0: Normal, 1: Return, 2: Change, 3: Refund
            'return_summary' => '',
            'can_open' => 1, // 1: Yes, 0: No
            'is_office_given' => 0, // 1: Yes, 0: No
            'shipment_on_sender' => 1, // 1: Yes, 0: No
            'extra_cost_payer' => 0, // 1: Sender, 0: Receiver,
            'is_fragile' => 'نعم',
            'is_measrable' => 'نعم',
        ];
    }

    public static function orderSummary($order)
    {
        $items = $order->items->map(function ($item) {
            $name = '';

            if($item->product_id){
                $name = $item->product->name;
            }

            if ($item->product_variant_id) {
                $variant = $item->product_variant;

                $name = $variant->product->name . ' (' . $variant->variant_name . ')';
            }

            $name = $item->quantity . ' x ' . $name;

            return $name;
        });

        return implode(" | ", $items->toArray());
    }

    public static function http($endpoint, $data = [], $method = 'POST')
    {

        $data['authentication_key'] = env('NAWRIS_AUTHENTICATION_KEY');
        $url = env('NAWRIS_API_URL') . $endpoint;

        $http = Http::withHeaders([

            'Content-Type' => 'application/json',
            'Accept' => 'application/json'

        ]);

        Log::info('Nawris Request: ', ['url' => $url, 'method' => $method, 'data' => $data]);

        switch ($method) {
            case 'POST':
                return $http->post($url, $data);
                break;
            case 'GET':
                return $http->get($url, $data);
                break;
            case 'PUT':
                return $http->put($url, $data);
                break;
            case 'DELETE':
                return $http->delete($url, $data);
                break;
            default:
                return $http->post($url, $data);
            break;
        }
    }


    public static function cities()
    {
        return self::http('get-government', [], 'GET')->json();
    }

    public static function areas($city_id)
    {
        return self::http('get-area/' . $city_id, [], 'GET')->json();
    }
}


// testing json
// $mockData = [
//     'success' => 1,
//     'result' => [
//         'code' => '1403820',
//         'invoice_number' => null,
//         'bar_code' => '1403820',
//         'remote_order_id' => null,
//     ],
// ];

// return new CustomJsonResponse($mockData, 200, ['Content-Type' => 'application/json']);
class CustomJsonResponse extends JsonResponse
{
    public function json()
    {
        return json_decode($this->getContent(), true);
    }

    public function successful()
    {
        return true;
    }
}