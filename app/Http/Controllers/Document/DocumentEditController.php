<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Request;
class DocumentEditController extends Controller
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

        
    }
    
}
