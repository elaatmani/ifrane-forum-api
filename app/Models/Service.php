<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Bookmarkable;

class Service extends Model
{
    use HasFactory, Bookmarkable;

    protected $fillable = ['name', 'slug', 'description', 'image', 'company_id', 'status'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class);
    }

}
