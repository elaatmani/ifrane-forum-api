<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'variant_name',
        'quantity',
        'stock_alert',
        'product_id'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
