<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VideoCallRoom extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'whereby_meeting_id',
        'room_url',
        'host_room_url',
        'call_type',
        'status',
        'created_by',
        'ended_at',
        'expires_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'ended_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function videoCalls()
    {
        return $this->hasMany(VideoCall::class, 'room_id');
    }

    public function participants()
    {
        return $this->hasMany(VideoCallParticipant::class, 'room_id');
    }

    public function activeParticipants()
    {
        return $this->hasMany(VideoCallParticipant::class, 'room_id')
                    ->where('status', 'joined');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeVideo($query)
    {
        return $query->where('call_type', 'video');
    }

    public function scopeVoice($query)
    {
        return $query->where('call_type', 'voice');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->where('expires_at', '>', now())
              ->orWhereNull('expires_at');
        });
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isEnded()
    {
        return $this->status === 'ended';
    }

    public function isVideo()
    {
        return $this->call_type === 'video';
    }

    public function isVoice()
    {
        return $this->call_type === 'voice';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getParticipantCount()
    {
        return $this->activeParticipants()->count();
    }

    public function hasParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function addParticipant($userId)
    {
        return $this->participants()->create([
            'user_id' => $userId,
            'status' => 'invited'
        ]);
    }

    public function markParticipantJoined($userId)
    {
        return $this->participants()
                    ->where('user_id', $userId)
                    ->update([
                        'status' => 'joined',
                        'joined_at' => now()
                    ]);
    }

    public function markParticipantLeft($userId)
    {
        return $this->participants()
                    ->where('user_id', $userId)
                    ->update([
                        'status' => 'left',
                        'left_at' => now()
                    ]);
    }

    public function endRoom($reason = 'ended_by_user')
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now()
        ]);

        // Mark all participants as left
        $this->activeParticipants()->update([
            'status' => 'left',
            'left_at' => now()
        ]);

        return $this;
    }

    public function extendExpiry($minutes = 30)
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes)
        ]);

        return $this;
    }
}

