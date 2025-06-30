<?php

namespace App\Http\Controllers\Document;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\DocumentRepository;
use App\Http\Requests\Document\DocumentStoreRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class DocumentStoreController extends Controller
{
    public function __construct(
        private readonly DocumentRepository $documentRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(DocumentStoreRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Get validated data
            $data = $request->validated();
            
            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Store file and get path
                $data['file_url'] = $this->storeFile($file, 'documents');
                
                // Override file metadata with actual file properties
                $data['size'] = (string) $file->getSize();
                $data['extension'] = $file->getClientOriginalExtension();
                $data['mime_type'] = $file->getMimeType();
                $data['type'] = $file->getClientOriginalExtension();
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $data['thumbnail_url'] = $this->storeFile($thumbnail, 'documents/thumbnails');
            }
            
            // Set created_by to current authenticated user
            $data['created_by'] = auth()->id();
            
            // Set company_id from user's company if not provided
            // if (!isset($data['company_id']) && auth()->user()->companies()->exists()) {
            //     $data['company_id'] = auth()->user()->companies()->first()->id;
            // }
            
            // Set default status if not provided
            $data['status'] = $data['status'] ?? 'inactive';
            
            // Create document record
            $document = $this->documentRepository->create($data);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Document created successfully',
                'data' => $document
            ], 201);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if document creation fails
            if (isset($data['file_url']) && Storage::disk('public')->exists($data['file_url'])) {
                Storage::disk('public')->delete($data['file_url']);
            }
            
            return response()->json([
                'message' => 'Failed to create document',
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
}
