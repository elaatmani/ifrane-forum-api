<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Rules\Order\CitySelected;
use App\Enums\OrderConfirmationEnum;
use App\Rules\Order\CityAreaSelected;
use App\Rules\Order\ProductOrVariant;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('order.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // get param id from route
        $order = Order::where('id', $this->route('id'))->first();

        return [
            'customer_city' => ['required', new CitySelected($this->agent_status)],
            'customer_area' => [new CityAreaSelected($this->agent_status, $this->customer_city)],

            'items' => ['required', 'array'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'calls' => ['integer', 'min:0'],
            'items.*' => [new ProductOrVariant()],

            'delivery_id' => [
                'nullable',
                function ($attribute, $value, $fail)  {
                    if (!in_array(request()->input('agent_status'), [OrderConfirmationEnum::CONFIRMED->value, OrderConfirmationEnum::CHANGE->value, OrderConfirmationEnum::REFUND->value]) && !is_null($value)) {
                        $fail('not-confirmed');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'calls.min' => 'invalid-calls',
            'items.*.quantity.min' => 'invalid-quantity',
            'items.required' => 'items-required'
        ];
    }
}
