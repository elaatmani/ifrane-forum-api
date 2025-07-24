<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Bookmarkable;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'thumbnail_url',
        'created_by',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}

