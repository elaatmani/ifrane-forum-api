<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class DocumentDeleteController extends Controller
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $document = $this->documentRepository->find($id);

        if (!$document) {
            return response()->json([
                'message' => 'Document not found'
            ], 404);
        }

        $this->documentRepository->delete($id);

        return response()->json([
            'message' => 'Document deleted successfully'
        ], 200);
    }
}
