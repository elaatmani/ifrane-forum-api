<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'buying_price',
        'selling_price',
        'is_active',
        'has_variants',
        'quantity',
        'stock_alert',
        'video_url',
        'store_url',
        'image_url',
        'created_by',
    ];

    protected $casts = [
        'buying_price' => 'double',
        'selling_price' => 'double',
        'is_active' => 'boolean',
        'has_variants' => 'boolean',
        'quantity' => 'integer',
        'stock_alert' => 'integer',
        'created_by' => 'integer',
    ];


    public function variants() {
        return $this->hasMany(ProductVariant::class);
    }

    public function offers() {
        return $this->hasMany(ProductOffer::class);
    }

    
    // has many cross product through product_cross table
    public function cross_products() {
        return $this->hasManyThrough(Product::class, ProductCross::class, 'product_id', 'id', 'id', 'cross_product_id');
    }

    // Direct relationship to ProductCross model
    public function product_crosses() {
        return $this->hasMany(ProductCross::class);
    }
}

