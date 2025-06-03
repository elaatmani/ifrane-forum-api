<?php

namespace App\Http\Controllers\Ad;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AdRepositoryInterface;

class AdUpdateController extends Controller
{
    protected $repository;

    public function __construct(AdRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'code' => 'SUCCESS',
        ]);
    }
}
