<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VideoCall extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'room_id',
        'conversation_id',
        'call_type',
        'status',
        'initiated_by',
        'accepted_by',
        'accepted_at',
        'rejected_at',
        'ended_at',
        'duration',
        'end_reason',
        'reject_reason'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    // Relationships
    public function room()
    {
        return $this->belongsTo(VideoCallRoom::class, 'room_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function accepter()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    // Scopes
    public function scopeInitiated($query)
    {
        return $query->where('status', 'initiated');
    }

    public function scopeRinging($query)
    {
        return $query->where('status', 'ringing');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'ringing', 'accepted']);
    }

    public function scopeVideo($query)
    {
        return $query->where('call_type', 'video');
    }

    public function scopeVoice($query)
    {
        return $query->where('call_type', 'voice');
    }

    // Helper methods
    public function isInitiated()
    {
        return $this->status === 'initiated';
    }

    public function isRinging()
    {
        return $this->status === 'ringing';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isEnded()
    {
        return $this->status === 'ended';
    }

    public function isMissed()
    {
        return $this->status === 'missed';
    }

    public function isActive()
    {
        return in_array($this->status, ['initiated', 'ringing', 'accepted']);
    }

    public function isVideo()
    {
        return $this->call_type === 'video';
    }

    public function isVoice()
    {
        return $this->call_type === 'voice';
    }

    public function accept($userId)
    {
        $this->update([
            'status' => 'accepted',
            'accepted_by' => $userId,
            'accepted_at' => now()
        ]);

        return $this;
    }

    public function reject($userId, $reason = 'declined_by_user')
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'reject_reason' => $reason
        ]);

        return $this;
    }

    public function end($duration = null, $reason = 'ended_by_user')
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $duration,
            'end_reason' => $reason
        ]);

        return $this;
    }

    public function markAsMissed($reason = 'no_answer')
    {
        $this->update([
            'status' => 'missed',
            'reject_reason' => $reason
        ]);

        return $this;
    }

    public function getDuration()
    {
        if ($this->accepted_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->accepted_at);
        }
        
        return $this->duration ?? 0;
    }

    public function isExpired()
    {
        // Check if call has been initiated for more than 60 seconds without response
        if ($this->isInitiated() || $this->isRinging()) {
            return $this->created_at->diffInSeconds(now()) > 60;
        }
        
        return false;
    }

    public function shouldAutoExpire()
    {
        return $this->isInitiated() || $this->isRinging();
    }
}

