<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use Illuminate\Http\Request;

class BookmarkDeleteController extends Controller
{
    protected $bookmarkRepository;

    public function __construct(BookmarkRepositoryInterface $bookmarkRepository)
    {
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Remove a bookmark for the authenticated user.
     */
    public function __invoke(Request $request, $bookmarkId)
    {
        try {
            $userId = auth()->id();

            // Find the bookmark
            $bookmark = $this->bookmarkRepository->find($bookmarkId);

            if (!$bookmark) {
                return response()->json([
                    'message' => 'Bookmark not found',
                    'code' => 'NOT_FOUND'
                ], 404);
            }

            // Ensure the bookmark belongs to the authenticated user
            if ($bookmark->user_id !== $userId) {
                return response()->json([
                    'message' => 'Unauthorized to delete this bookmark',
                    'code' => 'UNAUTHORIZED'
                ], 403);
            }

            // Delete the bookmark (soft delete)
            $this->bookmarkRepository->delete($bookmark);

            return response()->json([
                'message' => 'Bookmark removed successfully',
                'code' => 'SUCCESS'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove bookmark',
                'error' => $e->getMessage(),
                'code' => 'ERROR'
            ], 500);
        }
    }
} 