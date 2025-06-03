<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'nawris_city_id',
        'name',
        'shipping_cost'
    ];


    public function areas() {
        return $this->hasMany(CityArea::class);
    }
}
