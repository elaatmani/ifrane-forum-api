<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'nawris_area_id',
        'name',
        'shipping_cost'
    ];


    public function city() {
        return $this->belongsTo(City::class);
    }
}
