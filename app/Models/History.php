<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'history';

    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'actor_id',
        'body',
        'fields',
        'event'
    ];


    protected $casts = [
        'fields' => 'array'
    ];

    public function trackable()
    {
        return $this->morphTo('trackable', 'trackable_type', 'trackable_id');
    }

    public function toArray()
    {
        $array = parent::toArray();
        unset($array['updated_at']);
        unset($array['deleted_at']);
        unset($array['trackable_type']);
        unset($array['trackable_id']);
        return [
            ...$array,
            'action_by' => $this->user ? $this->user->name : "Not Found ($this->actor_id)"
        ];
    }

    public function user() {
        return $this->belongsTo(User::class, 'actor_id');
    }
}