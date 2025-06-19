<?php

namespace App\Http\Controllers\Company\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Http\Requests\Company\Admin\CompanyStoreRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyStoreController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(CompanyStoreRequest $request)
    {
        try {
            // Get all validated data
            $data = $request->validated();

            $data['user_ids'] = json_decode($data['user_ids'], true);
            $data['category_ids'] = json_decode($data['category_ids'], true);
            $data['certificate_ids'] = json_decode($data['certificate_ids'], true);
            
            // Handle logo file upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $this->storeFile($request->file('logo'), 'companies/logos');
            }
            
            // Handle background image file upload
            if ($request->hasFile('background_image')) {
                $data['background_image'] = $this->storeFile($request->file('background_image'), 'companies/backgrounds');
            }
            
            // Set created_by to current user ID
            $data['created_by'] = auth()->id();
            
            $company = $this->companyRepository->create($data);
            
            return response()->json([
                'message' => 'Company created successfully',
                'fields' => $data,
                'data' => $company
            ], 201);
            
        } catch (\Exception $e) {
            // Clean up any uploaded files if company creation fails
            if (isset($data['logo']) && Storage::disk('public')->exists($data['logo'])) {
                Storage::disk('public')->delete($data['logo']);
            }
            if (isset($data['background_image']) && Storage::disk('public')->exists($data['background_image'])) {
                Storage::disk('public')->delete($data['background_image']);
            }
            
            return response()->json([
                'message' => 'Failed to create company',
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
