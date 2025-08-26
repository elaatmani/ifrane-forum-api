<?php

namespace App\Models;

use App\Traits\Bookmarkable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function getLogoAttribute($value)
    {
        if($value && strpos($value, 'logos') !== false){
            return asset('storage/' . $value);
        } else if($value){
            return url($value);
        }

        return null;
    }

    public function getBackgroundImageAttribute($value)
    {
        if($value && strpos($value, 'backgrounds') !== false){
            return asset('storage/' . $value);
        } else if($value){
            return url($value);
        }   

        return null;
    }
}
