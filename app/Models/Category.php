<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class Category extends Model
{
    use HasFactory, SoftDeletes, Bookmarkable;

    protected $fillable = ['name', 'slug', 'type'];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
