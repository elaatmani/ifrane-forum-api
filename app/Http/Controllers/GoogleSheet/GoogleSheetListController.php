<?php

namespace App\Http\Controllers\GoogleSheet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GoogleSheet\GoogleSheetListResource;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetListController extends Controller
{

    protected $repository;

    public function __construct(GoogleSheetRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $sheets = $this->repository->paginate($per_page);
        $sheets->getCollection()->transform(fn($sheet) => new GoogleSheetListResource($sheet));

        return response()->json([
            'data' => $sheets,
            'code' => 'SUCCESS',
        ], 200);
    }
}
