<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Bookmarkable;

class Company extends Model
{
    use HasFactory, SoftDeletes, Bookmarkable;

    protected $fillable = [
        'name',
        'created_by',
        'streaming_platform',
        'logo',
        'background_image',
        'address',
        'primary_phone',
        'secondary_phone',
        'description',
        'primary_email',
        'secondary_email',
        'website',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'country_id',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get company conversations
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class)->where('type', 'company');
    }
}
