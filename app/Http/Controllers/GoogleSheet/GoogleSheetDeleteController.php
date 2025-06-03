<?php

namespace App\Http\Controllers\GoogleSheet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetDeleteController extends Controller
{

    protected $repository;

    public function __construct(GoogleSheetRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $this->repository->delete($id);

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Sheet deleted successfully'
        ], 200);
    }
}
