<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCross extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'cross_product_id',
        'price',
        'note'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cross_product() {
        return $this->belongsTo(Product::class, 'cross_product_id');
    }
    
    
    
}
