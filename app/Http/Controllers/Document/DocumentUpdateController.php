<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\DocumentUpdateRequest;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class DocumentUpdateController extends Controller
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(DocumentUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            // Find the existing document
            $document = $this->documentRepository->find($id);
            
            if (!$document) {
                return response()->json([
                    'message' => 'Document not found'
                ], 404);
            }
            
            // Get validated data
            $data = $request->validated();
            
            // Store old file paths for cleanup if update succeeds
            $oldFileUrl = null;
            $oldThumbnailUrl = null;
            
            // Handle file upload if is_file_updated is true
            if ($request->boolean('is_file_updated') && $request->hasFile('file')) {
                $file = $request->file('file');
                
                // Store old file URL for cleanup
                $oldFileUrl = $document->file_url;
                
                // Store new file and get path
                $data['file_url'] = $this->storeFile($file, 'documents');
                
                // Update file metadata with actual file properties
                $data['size'] = (string) $file->getSize();
                $data['extension'] = $file->getClientOriginalExtension();
                $data['mime_type'] = $file->getMimeType();
                $data['type'] = $file->getClientOriginalExtension();
            }

            // Handle thumbnail upload if is_thumbnail_updated is true
            if ($request->boolean('is_thumbnail_updated') && $request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                
                // Store old thumbnail URL for cleanup
                $oldThumbnailUrl = $document->thumbnail_url;
                
                // Store new thumbnail
                $data['thumbnail_url'] = $this->storeFile($thumbnail, 'documents/thumbnails');
            }
            
            // Remove the flags from data before updating
            unset($data['is_file_updated'], $data['is_thumbnail_updated']);
            
            // Remove read-only fields that shouldn't be manually updated
            if (!$request->boolean('is_file_updated')) {
                unset($data['type'], $data['size'], $data['extension'], $data['mime_type']);
            }
            
            // Update document record
            $updatedDocument = $this->documentRepository->update($id, $data);
            
            DB::commit();
            
            // Clean up old files after successful update
            $this->cleanupOldFiles($oldFileUrl, $oldThumbnailUrl);
            
            return response()->json([
                'message' => 'Document updated successfully',
                'data' => $updatedDocument
            ], 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            // Clean up newly uploaded files if update fails
            if (isset($data['file_url']) && Storage::disk('public')->exists($data['file_url'])) {
                Storage::disk('public')->delete($data['file_url']);
            }
            if (isset($data['thumbnail_url']) && Storage::disk('public')->exists($data['thumbnail_url'])) {
                Storage::disk('public')->delete($data['thumbnail_url']);
            }
            
            return response()->json([
                'message' => 'Failed to update document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a file and return the file path
     */
    private function storeFile($file, string $directory): string
    {
        // Generate unique filename
        $filename = time() . '_' . auth()->id() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Store file in the specified directory
        $path = $file->storeAs($directory, $filename, 'public');
        
        return $path;
    }
    
    /**
     * Clean up old files after successful update
     */
    private function cleanupOldFiles(?string $oldFileUrl, ?string $oldThumbnailUrl): void
    {
        // Delete old file if it exists and is different from new one
        if ($oldFileUrl && Storage::disk('public')->exists($oldFileUrl)) {
            Storage::disk('public')->delete($oldFileUrl);
        }
        
        // Delete old thumbnail if it exists and is different from new one
        if ($oldThumbnailUrl && Storage::disk('public')->exists($oldThumbnailUrl)) {
            Storage::disk('public')->delete($oldThumbnailUrl);
        }
    }
}
