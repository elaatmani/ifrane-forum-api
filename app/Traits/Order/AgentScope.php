<?php

namespace App\Traits\Order;

use App\Enums\OrderConfirmationEnum;

trait AgentScope
{
    public function scopeWhereNoAgent($query)
    {
        return $query->whereNull('agent_id');
    }

    public function scopeWhereNotAssigned($query)
    {
        return $query->where([
            'agent_id' => null,
            'agent_status' => OrderConfirmationEnum::NEW->value
        ]);
    }

    public function scopeWhereAgent($query, $id)
    {
        return $query->where('agent_id', $id);
    }

    public function scopeWhereAssignedNotConfirmed($query, $id)
    {
        return $query->where([
            'agent_id' => $id,
            'agent_status' => OrderConfirmationEnum::NEW->value
        ]);
    }
}
