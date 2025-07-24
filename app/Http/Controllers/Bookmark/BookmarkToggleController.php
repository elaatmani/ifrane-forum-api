<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookmark\BookmarkStoreRequest;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BookmarkToggleController extends Controller
{
    protected $bookmarkRepository;

    public function __construct(BookmarkRepositoryInterface $bookmarkRepository)
    {
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Toggle bookmark status for the authenticated user.
     * If bookmarked: removes it. If not bookmarked: adds it.
     */
    public function __invoke(BookmarkStoreRequest $request)
    {
        try {
            $data = $request->validated();
            $userId = auth()->id();

            // Validate that the bookmarkable item exists and is accessible
            $bookmarkableModel = $this->findBookmarkableModel($data);
            if (!$bookmarkableModel) {
                return response()->json([
                    'message' => 'Item not found or not accessible',
                    'code' => 'NOT_FOUND'
                ], 404);
            }

            // Check current bookmark status
            $isCurrentlyBookmarked = $this->bookmarkRepository->bookmarkExists(
                $userId,
                $data['bookmarkable_type'],
                $data['bookmarkable_id']
            );

            DB::beginTransaction();

            if ($isCurrentlyBookmarked) {
                // Remove bookmark
                $this->bookmarkRepository->removeBookmark(
                    $userId,
                    $data['bookmarkable_type'],
                    $data['bookmarkable_id']
                );

                DB::commit();

                return response()->json([
                    'message' => 'Bookmark removed successfully',
                    'bookmarked' => false,
                    'action' => 'removed',
                    'item' => [
                        'type' => $this->getSimpleType($data['bookmarkable_type']),
                        'id' => $data['bookmarkable_id']
                    ],
                    'code' => 'SUCCESS'
                ], 200);

            } else {
                // Add bookmark
                $bookmark = $this->bookmarkRepository->createBookmark(
                    $userId,
                    $data['bookmarkable_type'],
                    $data['bookmarkable_id']
                );

                DB::commit();

                return response()->json([
                    'message' => 'Item bookmarked successfully',
                    'bookmarked' => true,
                    'action' => 'added',
                    'bookmark' => [
                        'id' => $bookmark->id,
                        'type' => $this->getSimpleType($bookmark->bookmarkable_type),
                        'item_id' => $bookmark->bookmarkable_id,
                        'created_at' => $bookmark->created_at
                    ],
                    'code' => 'SUCCESS'
                ], 201);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to toggle bookmark',
                'error' => $e->getMessage(),
                'code' => 'ERROR'
            ], 500);
        }
    }

    /**
     * Find and validate the bookmarkable model.
     */
    private function findBookmarkableModel($data)
    {
        $modelClass = $this->normalizeBookmarkableType($data['bookmarkable_type']);
        
        if (!class_exists($modelClass)) {
            return null;
        }

        // Find the model instance
        $model = $modelClass::find($data['bookmarkable_id']);
        
        if (!$model) {
            return null;
        }

        // Basic accessibility check - ensure it's not soft deleted
        if (method_exists($model, 'trashed') && $model->trashed()) {
            return null;
        }

        return $model;
    }

    /**
     * Normalize bookmarkable type to full class name.
     */
    private function normalizeBookmarkableType($type)
    {
        if (str_contains($type, '\\')) {
            return $type;
        }

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
        // Handle both full class names and simple types
        if (!str_contains($fullClassName, '\\')) {
            return strtolower($fullClassName);
        }

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
} 