<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'platform',
        'user_id',
        'spend',
        'product_id',
        'spent_in',
        'leads',
        // 'stopped_at',
    ];


    public function product() {
        return $this->belongsTo(Product::class);
    }
}
