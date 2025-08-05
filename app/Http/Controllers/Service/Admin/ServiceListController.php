<?php

namespace App\Http\Controllers\Service\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Service\ServiceListResource;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceListController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $services = $this->serviceRepository->query();

        if ($request->has('search')) {
            $services->where('name', 'like', '%' . $request->search . '%');
            $services->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        $services = $services->paginate($request->per_page ?? 10);

        $services->getCollection()->transform(function ($service) {
            return new ServiceListResource($service);
        });

        return response()->json($services);
    }
}
