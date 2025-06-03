<?php

namespace App\Http\Controllers\GoogleSheet;

use App\Models\GoogleSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Google\GoogleSheetController;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetRefreshController extends Controller
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
        $sheets = GoogleSheet::active()->where('id', $id)->first();

        try {
            $data = app(GoogleSheetController::class)->syncSheet($sheets->id);
    
            return response()->json([
                'results' => $data,
                'code' => 'SUCCESS'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
                'trace' => $th->getTrace()
            ], 500);
        }
    }
}
