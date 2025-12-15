<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'meeting_type',
        'scheduled_at',
        'duration_minutes',
        'timezone',
        'organizer_id',
        'organizer_type',
        'user_id',
        'company_id',
        'whereby_meeting_id',
        'room_url',
        'host_room_url',
        'status',
        'notes',
        'metadata',
        'accepted_at',
        'declined_at',
        'cancelled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'duration_minutes' => 'integer',
    ];

    /**
     * Prevent updates to scheduled_at once the meeting exists.
     */
    public function setScheduledAtAttribute($value)
    {
        // Only allow setting scheduled_at on creation; ignore on updates
        if ($this->exists) {
            return;
        }

        $this->attributes['scheduled_at'] = $this->fromDateTime(
            $this->asDateTime($value)
        );
    }

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function acceptedParticipants()
    {
        return $this->participants()->where('status', 'accepted');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->whereIn('status', ['pending', 'accepted']);
    }

    public function scopeMemberToMember($query)
    {
        return $query->where('meeting_type', 'member_to_member');
    }

    public function scopeMemberToCompany($query)
    {
        return $query->where('meeting_type', 'member_to_company');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isUpcoming()
    {
        return $this->scheduled_at > now() && 
               in_array($this->status, ['pending', 'accepted']);
    }

    public function isPast()
    {
        return $this->scheduled_at < now();
    }

    public function canBeJoined()
    {
        return $this->isAccepted() && 
               $this->scheduled_at <= now()->addMinutes(15) && // 15 min before
               $this->scheduled_at >= now()->subHours(1); // 1 hour after
    }

    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getEndTime()
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function getTimeUntilMeeting()
    {
        return now()->diffInMinutes($this->scheduled_at);
    }
}