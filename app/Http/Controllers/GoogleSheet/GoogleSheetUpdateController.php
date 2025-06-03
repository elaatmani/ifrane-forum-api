<?php

namespace App\Http\Controllers\GoogleSheet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleSheet\UpdateGoogleSheetRequest;
use App\Http\Resources\GoogleSheet\GoogleSheetListResource;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetUpdateController extends Controller
{
    protected $repository;

    public function __construct(GoogleSheetRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateGoogleSheetRequest $request, $id)
    {
        $sheet = $this->repository->update($id, $request->validated(), $request->variants);
        $sheet = new GoogleSheetListResource($sheet);
        
        return response()->json([
            'data' => $sheet,
            'code' => 'SUCCESS',
        ], 200);
    }
}
