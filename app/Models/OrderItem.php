<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function product_variant() {
        return $this->belongsTo(ProductVariant::class);
    }

    public static function boot()
    {
        parent::boot();

        // static::creating(function ($orderItem) {
        //     if (is_null($orderItem->product_id) && is_null($orderItem->product_variant_id)) {
        //         throw new ValidationException('Either product_id or product_variant_id must be set.');
        //     }
        //     if (!is_null($orderItem->product_id) && !is_null($orderItem->product_variant_id)) {
        //         throw new ValidationException('Both product_id and product_variant_id cannot be set at the same time.');
        //     }
        // });
    }
}
