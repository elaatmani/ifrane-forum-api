<?php

namespace App\Models;

use App\Traits\Order\AgentScope;
use App\Traits\Order\StatusScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes, AgentScope, StatusScope;

    protected $fillable = [
        'google_sheet_id',
        'google_sheet_order_id',
        'google_sheet_order_date',

        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_area',
        'customer_notes',

        'agent_id',
        'agent_status',
        'agent_notes',
        
        'calls',
        
        'followup_id',
        'followup_assigned_at',
        'followup_status',
        'followup_calls',
        'reconfirmed_at',

        'delivery_id',
        'delivery_status',
        'order_sent_at',
        'order_delivered_at',
        'nawris_code',

        'invoice_id',
        'return_reason',
        'cancellation_reason',
        'cancellation_notes',

        'created_by',
    ];

    protected $casts = [
        'order_sent_at' => 'datetime',
        'order_delivered_at' => 'datetime',
        'calls' => 'integer',
        'followup_calls' => 'integer',
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function agent() {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function followup() {
        return $this->belongsTo(User::class, 'followup_id');
    }

    public function delivery() {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    public function google_sheet() {
        return $this->belongsTo(GoogleSheet::class, 'google_sheet_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $exists = static::where('google_sheet_id', $model->google_sheet_id)
                ->where('google_sheet_order_id', $model->google_sheet_order_id)
                ->whereNotNull('google_sheet_id')
                ->whereNotNull('google_sheet_order_id')
                ->exists();

            if ($exists) {
                \Log::info('Duplicate order attempt', $model->toArray());
                throw new \Exception('Order already exists cannot be duplicated.');
            }
        });
    }

    public function getAmountAttribute() {
        return $this->items->sum('price');
    }

    public function history()
    {
        return $this->morphMany(History::class, 'trackable');
    }
}
