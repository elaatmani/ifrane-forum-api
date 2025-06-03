<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sourcing extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'product_name',
        'product_url',
        'quantity',
        'destination_country',
        'note',
        'shipping_method',
        'status',
        'cost_per_unit',
        'shipping_cost',
        'additional_fees',
        'buying_price',
        'selling_price',
        'weight',
        'product_id',
        'sourcing_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(SourcingVariant::class);
    }

}
