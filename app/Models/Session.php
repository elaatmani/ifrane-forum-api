<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role', 'joined_at');
    }

    public function type()
    {
        return $this->belongsTo(Category::class, 'type_id')->where('type', 'type');
    }

    public function topic()
    {
        return $this->belongsTo(Category::class, 'topic_id')->where('type', 'topic');
    }

    public function language()
    {
        return $this->belongsTo(Category::class, 'language_id')->where('type', 'language');
    }

    /**
     * Get session conversation
     */
    public function conversation()
    {
        return $this->hasOne(Conversation::class)->where('type', 'session');
    }
}
