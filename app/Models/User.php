<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, Bookmarkable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login_times',
        'last_login_at',
        'last_action_at',
        'is_active',
        'is_completed',
        'acting_as_role',
        'profile_image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'login_times' => 'integer',
        'last_login_at' => 'datetime',
        'last_action_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user's custom notifications.
     */
    public function user_notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get the user's profile image URL.
     */
    public function getProfileImageAttribute($value)
    {
        if($value && strpos($value, 'assets/images/users') !== false){
            return url($value);
        } else if($value){
            return asset('storage/' . $value);
        }

        $colors = ['1abc9c', '3498db', '9b59b6', 'e67e22', 'e74c3c', '34495e', '16a085', '2980b9', '8e44ad', '2c3e50'];

        $index = crc32($this->name) % count($colors);
        $bgColor = $colors[$index];

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=ffffff&background=' . $bgColor;
    }
    
    /**
     * Get count of unread notifications.
     */
    public function unreadNotificationsCount()
    {
        return $this->user_notifications()->whereNull('read_at')->count();
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's bookmarks.
     */
    public function bookmarks()
    {
        return $this->hasMany(UserBookmark::class);
    }

    public function bookmarkedCompanies()
    {
        return $this->bookmarks()->where('bookmarkable_type', Company::class);
    }

    public function bookmarkedProducts()
    {
        return $this->bookmarks()->where('bookmarkable_type', Product::class);
    }

    public function bookmarkedServices()
    {
        return $this->bookmarks()->where('bookmarkable_type', Service::class);
    }

    public function bookmarkedSessions()
    {
        return $this->bookmarks()->where('bookmarkable_type', Session::class);
    }

    /**
     * Get actual Company models that are bookmarked by this user.
     */
    public function bookmarkedCompanyModels()
    {
        return Company::whereHas('bookmarks', function($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Get actual Product models that are bookmarked by this user.
     */
    public function bookmarkedProductModels()
    {
        return Product::whereHas('bookmarks', function($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Get actual Service models that are bookmarked by this user.
     */
    public function bookmarkedServiceModels()
    {
        return Service::whereHas('bookmarks', function($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Get actual Session models that are bookmarked by this user.
     */
    public function bookmarkedSessionModels()
    {
        return Session::whereHas('bookmarks', function($query) {
            $query->where('user_id', $this->id);
        });
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)
                    ->withPivot('role');
    }

    /**
     * Get the sessions that this user has joined.
     */
    public function sessions()
    {
        return $this->belongsToMany(Session::class)
                    ->withPivot('role', 'joined_at')->where('role', 'attendee');
    }

    /**
     * Get all connection requests sent by this user.
     */
    public function sentConnections()
    {
        return $this->hasMany(UserConnection::class, 'sender_id');
    }

    /**
     * Get all connection requests received by this user.
     */
    public function receivedConnections()
    {
        return $this->hasMany(UserConnection::class, 'receiver_id');
    }

    /**
     * Get all connections (sent and received) for this user.
     */
    public function allConnections()
    {
        return UserConnection::forUser($this->id);
    }

    /**
     * Get all accepted connections for this user.
     */
    public function acceptedConnections()
    {
        return $this->allConnections()->accepted();
    }

    /**
     * Get all pending connection requests sent by this user.
     */
    public function pendingSentConnections()
    {
        return $this->sentConnections()->pending();
    }

    /**
     * Get all pending connection requests received by this user.
     */
    public function pendingReceivedConnections()
    {
        return $this->receivedConnections()->pending();
    }

    /**
     * Check if this user has a connection with another user.
     */
    public function hasConnectionWith($userId)
    {
        return UserConnection::betweenUsers($this->id, $userId)
                            ->accepted()
                            ->exists();
    }

    /**
     * Check if this user has a pending connection request with another user.
     */
    public function hasPendingConnectionWith($userId)
    {
        return UserConnection::betweenUsers($this->id, $userId)
                            ->pending()
                            ->exists();
    }

    /**
     * Get connection status with another user.
     */
    public function getConnectionStatusWith($userId)
    {
        $connection = UserConnection::betweenUsers($this->id, $userId)->first();
        return $connection ? $connection->status : null;
    }

    /**
     * Send connection request to another user.
     */
    public function sendConnectionRequest($receiverId, $message)
    {
        return UserConnection::create([
            'sender_id' => $this->id,
            'receiver_id' => $receiverId,
            'message' => $message,
            'status' => UserConnection::STATUS_PENDING,
        ]);
    }

    /**
     * Get count of all connections for this user.
     */
    public function getConnectionsCount()
    {
        return $this->acceptedConnections()->count();
    }

    /**
     * Get count of pending connection requests received by this user.
     */
    public function getPendingConnectionRequestsCount()
    {
        return $this->pendingReceivedConnections()->count();
    }

    /**
     * Get mutual connections with another user.
     */
    public function getMutualConnectionsWith($userId)
    {
        $userConnections = User::find($userId)->acceptedConnections()
                              ->pluck('sender_id')
                              ->merge(User::find($userId)->acceptedConnections()->pluck('receiver_id'))
                              ->unique();

        $myConnections = $this->acceptedConnections()
                             ->pluck('sender_id')
                             ->merge($this->acceptedConnections()->pluck('receiver_id'))
                             ->unique();

        return $userConnections->intersect($myConnections);
    }


    /**
     * Get mutual connections with another user with (id, name, profile_image).
     */
    public function getMutualConnectionsWithProfile($userId)
    {
        $mutualConnectionIds = $this->getMutualConnectionsWith($userId);
        
        // Remove current user and target user from mutual connections
        $mutualConnectionIds = $mutualConnectionIds->reject(function($id) use ($userId) {
            return $id == $this->id || $id == $userId;
        });

        // Fetch User models for the mutual connection IDs
        $mutualUsers = User::whereIn('id', $mutualConnectionIds)->get();

        return $mutualUsers->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image,
            ];
        });
    }

    /**
     * Get user's conversations
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_users')
                    ->withPivot('last_read_at', 'joined_at')
                    ->withTimestamps();
    }

    public function videoCallRooms()
    {
        return $this->hasMany(\App\Models\VideoCallRoom::class, 'created_by');
    }

    public function initiatedVideoCalls()
    {
        return $this->hasMany(\App\Models\VideoCall::class, 'initiated_by');
    }

    public function acceptedVideoCalls()
    {
        return $this->hasMany(\App\Models\VideoCall::class, 'accepted_by');
    }

    public function videoCallParticipants()
    {
        return $this->hasMany(\App\Models\VideoCallParticipant::class);
    }

    /**
     * Get user's direct conversations
     */
    public function directConversations()
    {
        return $this->conversations()->where('type', 'direct');
    }

    /**
     * Get user's session conversations
     */
    public function sessionConversations()
    {
        return $this->conversations()->where('type', 'session');
    }

    /**
     * Get user's company conversations
     */
    public function companyConversations()
    {
        return $this->conversations()->where('type', 'company');
    }

    /**
     * Get messages sent by user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
