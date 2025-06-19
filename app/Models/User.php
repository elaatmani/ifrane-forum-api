<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

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
     * Get count of unread notifications.
     */
    public function unreadNotificationsCount()
    {
        return $this->user_notifications()->whereNull('read_at')->count();
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }
}
