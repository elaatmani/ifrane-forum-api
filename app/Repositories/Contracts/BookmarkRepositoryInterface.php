<?php

namespace App\Repositories\Contracts;

interface BookmarkRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get bookmarks for a specific user with optional filtering.
     */
    public function getUserBookmarks($userId, array $filters = []);

    /**
     * Create a bookmark for a user.
     */
    public function createBookmark($userId, $bookmarkableType, $bookmarkableId);

    /**
     * Remove a bookmark for a user.
     */
    public function removeBookmark($userId, $bookmarkableType, $bookmarkableId);

    /**
     * Check if a bookmark exists for a user.
     */
    public function bookmarkExists($userId, $bookmarkableType, $bookmarkableId);

    /**
     * Get bookmarks by type for a user.
     */
    public function getUserBookmarksByType($userId, $type);

    /**
     * Get bookmark counts by type for a user.
     */
    public function getUserBookmarkCounts($userId);

    /**
     * Get a specific bookmark by user and bookmarkable item.
     */
    public function findBookmarkByUserAndItem($userId, $bookmarkableType, $bookmarkableId);
} 