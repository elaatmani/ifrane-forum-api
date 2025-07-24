<?php

namespace App\Repositories\Eloquent;

use App\Models\UserBookmark;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BookmarkRepository extends BaseRepository implements BookmarkRepositoryInterface
{
    public function __construct(UserBookmark $model)
    {
        parent::__construct($model);
    }

    /**
     * Get bookmarks for a specific user with optional filtering.
     */
    public function getUserBookmarks($userId, array $filters = [])
    {
        $query = $this->model->forUser($userId)
                            ->withAccessibleBookmarkable()
                            ->orderBy('created_at', 'desc');

        // Filter by bookmarkable type
        if (isset($filters['type']) && $filters['type']) {
            $query->ofType($this->normalizeBookmarkableType($filters['type']));
        }

        // Search within bookmarked items (if search is provided)
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->whereHas('bookmarkable', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Return paginated or collection
        if (isset($filters['paginate']) && $filters['paginate']) {
            $perPage = $filters['per_page'] ?? 20;
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Create a bookmark for a user.
     */
    public function createBookmark($userId, $bookmarkableType, $bookmarkableId)
    {
        try {
            return UserBookmark::createForUser(
                $userId,
                $this->normalizeBookmarkableType($bookmarkableType),
                $bookmarkableId
            );
        } catch (\Exception $e) {
            // Handle duplicate bookmark attempts gracefully
            if (str_contains($e->getMessage(), 'unique_user_bookmark')) {
                return $this->findBookmarkByUserAndItem($userId, $bookmarkableType, $bookmarkableId);
            }
            throw $e;
        }
    }

    /**
     * Remove a bookmark for a user.
     */
    public function removeBookmark($userId, $bookmarkableType, $bookmarkableId)
    {
        return UserBookmark::removeForUser(
            $userId,
            $this->normalizeBookmarkableType($bookmarkableType),
            $bookmarkableId
        );
    }

    /**
     * Check if a bookmark exists for a user.
     */
    public function bookmarkExists($userId, $bookmarkableType, $bookmarkableId)
    {
        return UserBookmark::existsForUser(
            $userId,
            $this->normalizeBookmarkableType($bookmarkableType),
            $bookmarkableId
        );
    }

    /**
     * Get bookmarks by type for a user.
     */
    public function getUserBookmarksByType($userId, $type)
    {
        return $this->getUserBookmarks($userId, ['type' => $type]);
    }

    /**
     * Get bookmark counts by type for a user.
     */
    public function getUserBookmarkCounts($userId)
    {
        $counts = $this->model->forUser($userId)
                             ->withAccessibleBookmarkable()
                             ->select('bookmarkable_type', \DB::raw('count(*) as count'))
                             ->groupBy('bookmarkable_type')
                             ->pluck('count', 'bookmarkable_type')
                             ->toArray();

        // Normalize the keys to simple type names
        $normalizedCounts = [];
        foreach ($counts as $fullType => $count) {
            $simpleType = $this->getSimpleType($fullType);
            $normalizedCounts[$simpleType] = $count;
        }

        return $normalizedCounts;
    }

    /**
     * Get a specific bookmark by user and bookmarkable item.
     */
    public function findBookmarkByUserAndItem($userId, $bookmarkableType, $bookmarkableId)
    {
        return $this->model->where('user_id', $userId)
                          ->where('bookmarkable_type', $this->normalizeBookmarkableType($bookmarkableType))
                          ->where('bookmarkable_id', $bookmarkableId)
                          ->first();
    }

    /**
     * Normalize bookmarkable type to full class name.
     */
    private function normalizeBookmarkableType($type)
    {
        // If it's already a full class name, return as is
        if (str_contains($type, '\\')) {
            return $type;
        }

        // Map simple type names to full class names
        $typeMap = [
            'product' => 'App\\Models\\Product',
            'company' => 'App\\Models\\Company',
            'service' => 'App\\Models\\Service',
            'user' => 'App\\Models\\User',
            'document' => 'App\\Models\\Document',
            'sponsor' => 'App\\Models\\Sponsor',
            'category' => 'App\\Models\\Category',
            'certificate' => 'App\\Models\\Certificate',
        ];

        return $typeMap[strtolower($type)] ?? $type;
    }

    /**
     * Get simple type name from full class name.
     */
    private function getSimpleType($fullClassName)
    {
        $classMap = [
            'App\\Models\\Product' => 'product',
            'App\\Models\\Company' => 'company',
            'App\\Models\\Service' => 'service',
            'App\\Models\\User' => 'user',
            'App\\Models\\Document' => 'document',
            'App\\Models\\Sponsor' => 'sponsor',
            'App\\Models\\Category' => 'category',
            'App\\Models\\Certificate' => 'certificate',
        ];

        return $classMap[$fullClassName] ?? strtolower(class_basename($fullClassName));
    }

    /**
     * Get available bookmarkable types.
     */
    public function getAvailableTypes()
    {
        return [
            'product' => 'Products',
            'company' => 'Companies',
            'service' => 'Services',
            'user' => 'Users',
            'document' => 'Documents',
            'sponsor' => 'Sponsors',
            'category' => 'Categories',
            'certificate' => 'Certificates',
        ];
    }
} 