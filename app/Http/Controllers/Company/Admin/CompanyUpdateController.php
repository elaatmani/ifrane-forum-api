<?php

namespace App\Http\Controllers\Company\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Http\Requests\Company\Admin\CompanyUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyUpdateController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(CompanyUpdateRequest $request, $id)
    {
        try {
            // Check if company exists
            $existingCompany = $this->companyRepository->find($id);
            
            if (!$existingCompany) {
                return response()->json([
                    'message' => 'Company not found'
                ], 404);
            }

            // Get all validated data
            $data = $request->validated();

            // Process JSON arrays if provided
            if (isset($data['user_ids'])) {
                $data['user_ids'] = json_decode($data['user_ids'], true);
            }
            
            if (isset($data['category_ids'])) {
                $data['category_ids'] = json_decode($data['category_ids'], true);
            }
            
            if (isset($data['certificate_ids'])) {
                $data['certificate_ids'] = json_decode($data['certificate_ids'], true);
            }
            
            // Handle logo file upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $this->storeFile($request->file('logo'), 'companies/logos');
            }
            
            // Handle background image file upload
            if ($request->hasFile('background_image')) {
                $data['background_image'] = $this->storeFile($request->file('background_image'), 'companies/backgrounds');
            }
            
            // Update the company
            $company = $this->companyRepository->update($id, $data);
            
            if (!$company) {
                return response()->json([
                    'message' => 'Failed to update company'
                ], 500);
            }
            
            return response()->json([
                'message' => 'Company updated successfully',
                'fields' => $data,
                'data' => $company
            ], 200);
            
        } catch (\Exception $e) {
            // Clean up any uploaded files if company update fails
            if (isset($data['logo']) && Storage::disk('public')->exists($data['logo'])) {
                Storage::disk('public')->delete($data['logo']);
            }
            if (isset($data['background_image']) && Storage::disk('public')->exists($data['background_image'])) {
                Storage::disk('public')->delete($data['background_image']);
            }
            
            return response()->json([
                'message' => 'Failed to update company',
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
