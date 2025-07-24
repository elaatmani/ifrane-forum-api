<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookmark\BookmarkStoreRequest;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BookmarkStoreController extends Controller
{
    protected $bookmarkRepository;

    public function __construct(BookmarkRepositoryInterface $bookmarkRepository)
    {
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Add a bookmark for the authenticated user.
     */
    public function __invoke(BookmarkStoreRequest $request)
    {
        try {
            $data = $request->validated();
            $userId = auth()->id();

            // Check if bookmark already exists
            $exists = $this->bookmarkRepository->bookmarkExists(
                $userId,
                $data['bookmarkable_type'],
                $data['bookmarkable_id']
            );

            if ($exists) {
                return response()->json([
                    'message' => 'Item is already bookmarked',
                    'code' => 'ALREADY_BOOKMARKED'
                ], 409);
            }

            // Validate that the bookmarkable item exists and is accessible
            $bookmarkableModel = $this->findBookmarkableModel($data);
            if (!$bookmarkableModel) {
                return response()->json([
                    'message' => 'Item not found or not accessible',
                    'code' => 'NOT_FOUND'
                ], 404);
            }

            // Create the bookmark
            DB::beginTransaction();
            
            $bookmark = $this->bookmarkRepository->createBookmark(
                $userId,
                $data['bookmarkable_type'],
                $data['bookmarkable_id']
            );

            DB::commit();

            return response()->json([
                'message' => 'Item bookmarked successfully',
                'bookmark' => [
                    'id' => $bookmark->id,
                    'bookmarkable_type' => $this->getSimpleType($bookmark->bookmarkable_type),
                    'bookmarkable_id' => $bookmark->bookmarkable_id,
                    'created_at' => $bookmark->created_at
                ],
                'code' => 'SUCCESS'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to bookmark item',
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

        // Additional access checks could be added here based on your business logic
        // For example, checking if user has permission to view this content

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