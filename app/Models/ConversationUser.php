<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    protected $table = 'conversation_users';

    protected $fillable = [
        'conversation_id', 'user_id', 'last_read_at', 'joined_at'
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime'
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 