<?php

namespace App\Traits;

use App\Models\UserBookmark;

trait Bookmarkable
{
    /**
     * Get all bookmarks for this model.
     */
    public function bookmarks()
    {
        return $this->morphMany(UserBookmark::class, 'bookmarkable');
    }

    /**
     * Get users who bookmarked this model.
     */
    public function bookmarkedBy()
    {
        return $this->morphMany(UserBookmark::class, 'bookmarkable')
                    ->with('user');
    }

    /**
     * Check if this model is bookmarked by the current authenticated user.
     */
    public function isBookmarked(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->isBookmarkedBy(auth()->id());
    }

    /**
     * Check if this model is bookmarked by a specific user.
     */
    public function isBookmarkedBy($userId): bool
    {
        if (!$userId) {
            return false;
        }

        return UserBookmark::existsForUser(
            $userId,
            get_class($this),
            $this->id
        );
    }

    /**
     * Get the bookmark record for the current authenticated user.
     */
    public function getBookmarkForCurrentUser()
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->getBookmarkForUser(auth()->id());
    }

    /**
     * Get the bookmark record for a specific user.
     */
    public function getBookmarkForUser($userId)
    {
        if (!$userId) {
            return null;
        }

        return UserBookmark::where('user_id', $userId)
                          ->where('bookmarkable_type', get_class($this))
                          ->where('bookmarkable_id', $this->id)
                          ->first();
    }

    /**
     * Add bookmark for the current authenticated user.
     */
    public function addBookmark()
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->addBookmarkForUser(auth()->id());
    }

    /**
     * Add bookmark for a specific user.
     */
    public function addBookmarkForUser($userId)
    {
        if (!$userId) {
            return null;
        }

        return UserBookmark::createForUser(
            $userId,
            get_class($this),
            $this->id
        );
    }

    /**
     * Remove bookmark for the current authenticated user.
     */
    public function removeBookmark()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->removeBookmarkForUser(auth()->id());
    }

    /**
     * Remove bookmark for a specific user.
     */
    public function removeBookmarkForUser($userId)
    {
        if (!$userId) {
            return false;
        }

        return UserBookmark::removeForUser(
            $userId,
            get_class($this),
            $this->id
        );
    }

    /**
     * Toggle bookmark status for the current authenticated user.
     */
    public function toggleBookmark()
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->toggleBookmarkForUser(auth()->id());
    }

    /**
     * Toggle bookmark status for a specific user.
     */
    public function toggleBookmarkForUser($userId)
    {
        if (!$userId) {
            return null;
        }

        if ($this->isBookmarkedBy($userId)) {
            $this->removeBookmarkForUser($userId);
            return false; // Removed
        } else {
            $this->addBookmarkForUser($userId);
            return true; // Added
        }
    }

    /**
     * Get count of users who bookmarked this model.
     */
    public function getBookmarksCount()
    {
        return $this->bookmarks()->count();
    }

    /**
     * Scope to get models that are bookmarked by a specific user.
     */
    public function scopeBookmarkedBy($query, $userId)
    {
        return $query->whereHas('bookmarks', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
} 