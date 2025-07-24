<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes, Bookmarkable;

    protected $fillable = [
        'name',
        'image',
        'link',
        'description',
        'is_active',
    ];
}
