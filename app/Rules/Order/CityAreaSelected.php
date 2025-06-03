<?php

namespace App\Rules\Order;

use Closure;
use App\Models\City;
use App\Enums\OrderConfirmationEnum;
use Illuminate\Contracts\Validation\ValidationRule;

class CityAreaSelected implements ValidationRule
{

    public $agent_status;
    public $customer_city;

    public function __construct($agent_status,  $customer_city) {
        $this->agent_status = $agent_status;
        $this->customer_city = $customer_city;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $agent_status = $this->agent_status;
        $customer_city = $this->customer_city;
        // Check if agent_status is 'confirmed' and delivery_id is not null
        if (in_array($agent_status, [OrderConfirmationEnum::CONFIRMED->value, OrderConfirmationEnum::CHANGE->value, OrderConfirmationEnum::REFUND->value])) { 
            // Check if customer_city exists
            $city = City::where('name', $customer_city)->first();
            // abort(500, json_encode($value));

            if(!$city) {
                $fail('invalid');
            }

            if($city) {
                if($city->areas()->count()) {
                    if(!$city->areas()->where('name', $value)->exists()) {
                        $fail('invalid');
                    }
                }
            }

        }
    }
}
