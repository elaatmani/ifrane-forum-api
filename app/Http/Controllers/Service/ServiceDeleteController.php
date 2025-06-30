<?php

namespace App\Http\Controllers\Service;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceDeleteController extends Controller
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

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }
}
