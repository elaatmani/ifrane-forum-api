<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItem\OrderItemResource;

class OrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $deliveryTime = DB::table('history')
        // ->selectRaw("
        //     MAX(CASE WHEN JSON_CONTAINS(fields, '{\"field\": \"nawris_code\", \"old_value\": null}') THEN created_at ELSE NULL END) as start_date,
        //     MAX(CASE WHEN JSON_CONTAINS(fields, '{\"field\": \"delivery_status\", \"new_value\": \"delivered\"}') THEN created_at ELSE NULL END) as end_date
        // ")
        // ->where('trackable_id', $this->id)
        // ->first();
        
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_city' => $this->customer_city,
            'customer_area' => $this->customer_area,
            'nawris_code' => $this->nawris_code,
            'customer_notes' => $this->customer_notes,
            'agent_status' => $this->agent_status,
            'agent_id' => $this->agent_id,
            'followup_status' => $this->followup_status,
            'followup_id' => $this->followup_id,
            'followup_calls' => $this->followup_calls,
            'agent_notes' => $this->agent_notes,
            'delivery_id' => $this->delivery_id,
            'delivery_status' => $this->delivery_status,
            'calls' => $this->calls,
            'order_sent_at' => $this->order_sent_at,
            'order_delivered_at' => $this->order_delivered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reconfirmed_at' => $this->reconfirmed_at,
            'cancellation_reason' => $this->cancellation_reason,
            'cancellation_notes' => $this->cancellation_notes,

            'delivery_started_at' => null,//$deliveryTime->start_date,
            'delivery_ended_at' => null,//$deliveryTime->end_date,
            
            'items' => $this->items->map(fn($item) => new OrderItemResource($item))
        ];
    }
}
