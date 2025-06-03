<?php

namespace App\Traits\Order;

use App\Enums\OrderConfirmationEnum;

trait StatusScope
{
    public function scopeConfirmed($query)
    {
        return $query->where('agent_status', OrderConfirmationEnum::CONFIRMED->value);
    }

    public function scopeCanceled($query)
    {
        return $query->where('agent_status', OrderConfirmationEnum::CANCELED->value);
    }
}
