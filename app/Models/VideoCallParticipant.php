<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VideoCallParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'room_id',
        'user_id',
        'joined_at',
        'left_at',
        'status'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    // Relationships
    public function room()
    {
        return $this->belongsTo(VideoCallRoom::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeInvited($query)
    {
        return $query->where('status', 'invited');
    }

    public function scopeJoined($query)
    {
        return $query->where('status', 'joined');
    }

    public function scopeLeft($query)
    {
        return $query->where('status', 'left');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['invited', 'joined']);
    }

    // Helper methods
    public function isInvited()
    {
        return $this->status === 'invited';
    }

    public function isJoined()
    {
        return $this->status === 'joined';
    }

    public function isLeft()
    {
        return $this->status === 'left';
    }

    public function isActive()
    {
        return in_array($this->status, ['invited', 'joined']);
    }

    public function join()
    {
        $this->update([
            'status' => 'joined',
            'joined_at' => now()
        ]);

        return $this;
    }

    public function leave()
    {
        $this->update([
            'status' => 'left',
            'left_at' => now()
        ]);

        return $this;
    }

    public function getTimeInCall()
    {
        if ($this->joined_at && $this->left_at) {
            return $this->joined_at->diffInSeconds($this->left_at);
        }
        
        if ($this->joined_at && !$this->left_at) {
            return $this->joined_at->diffInSeconds(now());
        }
        
        return 0;
    }
}

