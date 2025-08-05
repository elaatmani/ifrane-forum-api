<?php

namespace App\Http\Controllers\Service\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Service\Admin\ServiceShowResource;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Http\Request;

class ServiceEditController extends Controller
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

        if (!$service) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }

        $service = new ServiceShowResource($service);

        return response()->json([
            'data' => $service,
            'code' => 'SUCCESS',
        ], 200);
    }
}
