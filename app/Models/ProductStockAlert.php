<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'notified_at',
        'resolved_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'notified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the product associated with the alert.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active alerts.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope a query to only include resolved alerts.
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }
} 