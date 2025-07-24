<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_active',
        'name',
        'description',
        'image',
        'status',
        'start_date',
        'end_date',
        'link',
        'type_id',
        'topic_id',
        'language_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role');
    }
}
