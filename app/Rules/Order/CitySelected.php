<?php

namespace App\Rules\Order;

use App\Enums\OrderConfirmationEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\City;

class CitySelected implements ValidationRule
{
    public $agent_status;

    public function __construct($agent_status) {
        $this->agent_status = $agent_status;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $agent_status = $this->agent_status;
        // Check if agent_status is 'confirmed' and delivery_id is not null
        if (in_array($agent_status, [OrderConfirmationEnum::CONFIRMED->value, OrderConfirmationEnum::CHANGE->value, OrderConfirmationEnum::REFUND->value])) { 
            // Check if customer_city exists
            $city = City::where('name', $value)->first();
            if (!$city) {
                $fail('invalid');
            }
        }
    }
}
