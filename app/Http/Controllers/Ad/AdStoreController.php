<?php

namespace App\Http\Controllers\Ad;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ad\StoreAdRequest;
use App\Http\Resources\Ad\AdListResource;
use App\Repositories\Contracts\AdRepositoryInterface;

class AdStoreController extends Controller
{
    protected $repository;

    public function __construct(AdRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreAdRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = auth()->id();


        $ad = $this->repository->create($data);

        return response()->json([
            'ad' => new AdListResource($ad),
            'code' => 'SUCCESS',
        ]);
    }
}
