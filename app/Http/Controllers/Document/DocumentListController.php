<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Http\Resources\Document\DocumentListResource;

class DocumentListController extends Controller
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $documents = $this->documentRepository->query();

        if ($request->has('search')) {
            $documents->where('name', 'like', '%' . $request->search . '%');
            $documents->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        $documents = $documents->paginate($request->per_page ?? 10);

        $documents->getCollection()->transform(function ($document) {
            return new DocumentListResource($document);
        });

        return $documents;
    }
}
