<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'bookmarkable_id' => 'integer',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarkable model (polymorphic relationship).
     */
    public function bookmarkable()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter bookmarks by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter bookmarks by bookmarkable type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('bookmarkable_type', $type);
    }

    /**
     * Scope to get bookmarks with their bookmarkable models that exist.
     */
    public function scopeWithAccessibleBookmarkable($query)
    {
        return $query->with('bookmarkable')
                    ->whereHas('bookmarkable', function($q) {
                        // This will automatically exclude deleted bookmarkable items
                        // Laravel's whereHas respects soft deletes on related models that use soft deletes
                    });
    }

    /**
     * Check if a specific bookmark exists for a user and bookmarkable item.
     */
    public static function existsForUser($userId, $bookmarkableType, $bookmarkableId)
    {
        return static::where('user_id', $userId)
                    ->where('bookmarkable_type', $bookmarkableType)
                    ->where('bookmarkable_id', $bookmarkableId)
                    ->exists();
    }

    /**
     * Create or find a bookmark for a user and bookmarkable item.
     */
    public static function createForUser($userId, $bookmarkableType, $bookmarkableId)
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'bookmarkable_type' => $bookmarkableType,
            'bookmarkable_id' => $bookmarkableId,
        ]);
    }

    /**
     * Remove a bookmark for a user and bookmarkable item.
     */
    public static function removeForUser($userId, $bookmarkableType, $bookmarkableId)
    {
        return static::where('user_id', $userId)
                    ->where('bookmarkable_type', $bookmarkableType)
                    ->where('bookmarkable_id', $bookmarkableId)
                    ->delete();
    }
}
