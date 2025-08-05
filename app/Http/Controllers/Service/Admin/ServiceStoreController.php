<?php

namespace App\Http\Controllers\Service\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\Admin\ServiceStoreRequest;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            // Handle image file upload
            if ($request->hasFile('image')) {
                $data['image'] = $this->storeFile($request->file('image'), 'services');
            }

            // Extract category_ids for separate handling
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);

            // Create the service
            $service = $this->serviceRepository->create($data);

            // Attach categories if provided
            if (!empty($categoryIds)) {
                $service->categories()->attach($categoryIds);
            }

            return response()->json([
                'message' => 'Service created successfully',
                'data' => $service->load('categories', 'company')
            ], 201);

        } catch (\Exception $e) {
            // Clean up any uploaded files if service creation fails
            if (isset($data['image']) && Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            return response()->json([
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a file and return the file path
     */
    private function storeFile($file, string $directory): string
    {
        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Store file in the specified directory
        $path = $file->storeAs($directory, $filename, 'public');
        
        return $path;
    }
}
