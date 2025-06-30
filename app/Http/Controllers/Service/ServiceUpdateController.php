<?php

namespace App\Http\Controllers\Service;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceUpdateRequest;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceUpdateController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(ServiceUpdateRequest $request, $id)
    {
        try {
            $service = $this->serviceRepository->find($id);

            if (!$service) {
                return response()->json([
                    'message' => 'Service not found'
                ], 404);
            }

            // Get all validated data
            $data = $request->validated();

            // Process JSON array for categories if provided
            if (isset($data['categories'])) {
                $data['categories'] = json_decode($data['categories'], true);
            }

            // Handle image file upload based on is_image_updated flag
            if (isset($data['is_image_updated']) && $data['is_image_updated'] === 'true') {
                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->store('services', 'public');
                }
            }

            // Remove is_image_updated from data as it's not a model field
            unset($data['is_image_updated']);

            $updatedService = $this->serviceRepository->update($id, $data);

            return response()->json([
                'message' => 'Service updated successfully',
                'data' => $updatedService
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update service',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
