<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class Certificate extends Model
{
    use HasFactory, SoftDeletes, Bookmarkable;

    protected $fillable = ['name', 'code', 'description', 'type', 'slug'];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}
