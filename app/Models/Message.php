<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'conversation_id', 'sender_id', 'content', 'message_type', 'file_url', 'metadata'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array'
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
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeText($query)
    {
        return $query->where('message_type', 'text');
    }

    public function scopeFile($query)
    {
        return $query->where('message_type', 'file');
    }

    public function scopeCall($query)
    {
        return $query->whereIn('message_type', [
            'missed_call', 'video_call_request', 'voice_call_request', 
            'call_ended', 'call_rejected', 'call_accepted'
        ]);
    }

    public function scopeSystem($query)
    {
        return $query->where('message_type', 'system');
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Helper methods
    public function isText()
    {
        return $this->message_type === 'text';
    }

    public function isFile()
    {
        return $this->message_type === 'file';
    }

    public function isCall()
    {
        return in_array($this->message_type, [
            'missed_call', 'video_call_request', 'voice_call_request', 
            'call_ended', 'call_rejected', 'call_accepted'
        ]);
    }

    public function isSystem()
    {
        return $this->message_type === 'system';
    }

    public function isMissedCall()
    {
        return $this->message_type === 'missed_call';
    }

    public function isVideoCallRequest()
    {
        return $this->message_type === 'video_call_request';
    }

    public function isVoiceCallRequest()
    {
        return $this->message_type === 'voice_call_request';
    }

    public function isCallEnded()
    {
        return $this->message_type === 'call_ended';
    }

    public function isCallRejected()
    {
        return $this->message_type === 'call_rejected';
    }

    public function isCallAccepted()
    {
        return $this->message_type === 'call_accepted';
    }

    public function getFileUrl()
    {
        if ($this->isFile() && $this->file_url) {
            return asset('storage/' . $this->file_url);
        }
        return null;
    }
} 