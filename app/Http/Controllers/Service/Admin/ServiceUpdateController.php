<?php

namespace App\Http\Controllers\Service\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceUpdateRequest;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $data = $request->validated();

            // Handle image file upload
            if ($request->hasFile('image')) {
                $data['image'] = $this->storeFile($request->file('image'), 'services');
            }

            // Handle categories if provided
            if ($request->has('categories')) {
                $data['categories'] = json_decode($request->categories, true);
            }

            // Update the service
            $service = $this->serviceRepository->update($id, $data);

            if (!$service) {
                return response()->json([
                    'message' => 'Service not found',
                    'error' => 'The requested service could not be found'
                ], 404);
            }

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Service updated successfully',
                'service' => $service->load('categories', 'company')
            ], 200);

        } catch (\Exception $e) {
            // Clean up any uploaded files if service update fails
            if (isset($data['image']) && Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            return response()->json([
                'message' => 'Failed to update service',
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
