<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Conversation extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'type', 'name', 'created_by', 'session_id', 'company_id', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users')
                    ->withPivot('last_read_at', 'joined_at')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes for different types
    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeSession($query)
    {
        return $query->where('type', 'session');
    }

    public function scopeCompany($query)
    {
        return $query->where('type', 'company');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function isDirect()
    {
        return $this->type === 'direct';
    }

    public function isSession()
    {
        return $this->type === 'session';
    }

    public function isCompany()
    {
        return $this->type === 'company';
    }

    public function getOtherUser(User $currentUser)
    {
        if (!$this->isDirect()) {
            return null;
        }

        return $this->users()->where('user_id', '!=', $currentUser->id)->first();
    }

    public function markAsRead(User $user)
    {
        $this->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);
    }

    public function getUnreadCount(User $user)
    {
        $lastRead = $this->users()->where('user_id', $user->id)->first()->pivot->last_read_at;
        
        if (!$lastRead) {
            // Count messages from other users only
            return $this->messages()->where('sender_id', '!=', $user->id)->count();
        }

        // Count messages from other users created after last read
        return $this->messages()
            ->where('created_at', '>', $lastRead)
            ->where('sender_id', '!=', $user->id)
            ->count();
    }
} 