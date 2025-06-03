<?php

namespace App\Http\Controllers\GoogleSheet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleSheet\StoreGoogleSheetRequest;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetStoreController extends Controller
{
    protected $repository;

    public function __construct(GoogleSheetRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreGoogleSheetRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $sheet = $this->repository->create($validated);

        return response()->json([
            'code' => 'SUCCESS',
            'sheet' => $sheet,
            'message' => 'Sheet created successfully',
        ], 201);
    }
}
