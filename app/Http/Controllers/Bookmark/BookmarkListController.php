<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Resources\Bookmark\BookmarkListResource;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use Illuminate\Http\Request;

class BookmarkListController extends Controller
{
    protected $bookmarkRepository;

    public function __construct(BookmarkRepositoryInterface $bookmarkRepository)
    {
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Get bookmarks for the authenticated user.
     */
    public function __invoke(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->input('per_page', 20);
            $maxPerPage = 100;
            
            // Ensure per_page doesn't exceed maximum
            $perPage = min($perPage, $maxPerPage);

            // Prepare filters
            $filters = [
                'paginate' => true,
                'per_page' => $perPage
            ];

            // Filter by type
            if ($request->has('type') && $request->type) {
                $filters['type'] = $request->type;
            }

            // Search within bookmarked items
            if ($request->has('search') && $request->search) {
                $filters['search'] = $request->search;
            }

            // Get bookmarks
            $bookmarks = $this->bookmarkRepository->getUserBookmarks($userId, $filters);

            // Filter out items where bookmarkable is null (deleted/inaccessible items)
            $bookmarks->getCollection()->transform(function ($bookmark) {
                // If bookmarkable is null, skip this bookmark
                return $bookmark->bookmarkable ? new BookmarkListResource($bookmark) : null;
            })->filter(); // Remove null entries

            // Get bookmark counts by type for additional metadata
            $bookmarkCounts = $this->bookmarkRepository->getUserBookmarkCounts($userId);

            // Get available types
            $availableTypes = $this->bookmarkRepository->getAvailableTypes();

            return response()->json([
                'data' => $bookmarks,
                'meta' => [
                    'counts_by_type' => $bookmarkCounts,
                    'total_bookmarks' => array_sum($bookmarkCounts),
                    'available_types' => $availableTypes,
                    'filters_applied' => [
                        'type' => $request->type,
                        'search' => $request->search,
                    ]
                ],
                'code' => 'SUCCESS'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve bookmarks',
                'error' => $e->getMessage(),
                'code' => 'ERROR'
            ], 500);
        }
    }
} 