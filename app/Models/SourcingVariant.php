<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourcingVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_name',
        'quantity',
        'product_variant_id',
        'sourcing_id'
    ];

    public function sourcing()
    {
        return $this->belongsTo(Sourcing::class);
    }
}
