<?php

namespace App\Http\Controllers\Service;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceStoreRequest;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceStoreController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(ServiceStoreRequest $request)
    {
        try {
            // Get all validated data
            $data = $request->validated();

            // Process JSON array for categories if provided
            if (isset($data['categories'])) {
                $data['categories'] = json_decode($data['categories'], true);
            }

            // Handle image file upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('services', 'public');
            }

            // Set created_by to current user ID (if applicable)
            if (auth()->check()) {
                $data['created_by'] = auth()->id();
            }

            $service = $this->serviceRepository->create($data);

            return response()->json([
                'message' => 'Service created successfully',
                'data' => $service
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
