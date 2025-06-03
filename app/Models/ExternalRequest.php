<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExternalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'actor_id',
        'url',
        'method',
        'headers',
        'body',
        'response_code',
        'response_body',
    ];

    public function actor() {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
