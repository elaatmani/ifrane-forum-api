<?php

namespace App\Http\Controllers\Service;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Service\ServiceListResource;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceShowController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $service = $this->serviceRepository->find($id);

        return response()->json(new ServiceListResource($service));
    }
}
