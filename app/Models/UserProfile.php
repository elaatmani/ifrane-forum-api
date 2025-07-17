<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'country_id',
        'about',
        'linkedin_url',
        'instagram_url',
        'twitter_url',
        'facebook_url',
        'youtube_url',
        'github_url',
        'website_url',
        'address',
        'contact_email',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country associated with the profile.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
