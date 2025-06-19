<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    public function __construct(Company $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        
        try {
            $company = $this->model->create($data);
            $company->categories()->attach($data['category_ids']);
            $company->certificates()->attach($data['certificate_ids']);
            $company->users()->attach($data['user_ids']);
            
            DB::commit();
            return $company;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $company = $this->find($id);
            
            if (!$company) {
                DB::rollback();
                return null;
            }

            // Store old file paths for cleanup
            $oldLogo = $company->logo;
            $oldBackgroundImage = $company->background_image;

            // Update the company
            $company->update($data);

            // Handle relationships - use sync instead of attach for updates
            if (isset($data['category_ids'])) {
                $company->categories()->sync($data['category_ids']);
            }
            
            if (isset($data['certificate_ids'])) {
                $company->certificates()->sync($data['certificate_ids']);
            }
            
            if (isset($data['user_ids'])) {
                $company->users()->sync($data['user_ids']);
            }

            // Clean up old files if new ones were uploaded
            if (isset($data['logo']) && $oldLogo && $oldLogo !== $data['logo']) {
                $this->deleteFile($oldLogo);
            }
            
            if (isset($data['background_image']) && $oldBackgroundImage && $oldBackgroundImage !== $data['background_image']) {
                $this->deleteFile($oldBackgroundImage);
            }
            
            DB::commit();
            return $company;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delete($id)
    {
        // Check if $id is already a model instance
        if ($id instanceof Company) {
            $company = $id;
        } else {
            // Otherwise, treat it as an ID and find the record
            $company = $this->find($id);
        }

        if ($company) {
            // Delete associated files from storage
            $this->deleteCompanyFiles($company);
            
            // Delete the company record (soft delete due to SoftDeletes trait)
            return $company->delete();
        }
        
        return false;
    }

    /**
     * Delete company logo and background image files from storage
     */
    public function deleteCompanyFiles($company): void
    {
        // Delete logo file if it exists
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        // Delete background image file if it exists
        if ($company->background_image && Storage::disk('public')->exists($company->background_image)) {
            Storage::disk('public')->delete($company->background_image);
        }
    }

    /**
     * Delete a single file from storage
     */
    private function deleteFile($filePath): void
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Get filtered and paginated companies
     */
    public function getFilteredCompanies(array $filters, int $perPage = 10)
    {
        $query = $this->model->query()
            ->with(['categories', 'certificates', 'country', 'users']);

        // Text search across name, primary_email, and description
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('primary_email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by category IDs
        if (!empty($filters['category_id']) && is_array($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->whereIn('categories.id', $filters['category_id']);
            });
        }

        // Filter by country IDs
        if (!empty($filters['country_id']) && is_array($filters['country_id'])) {
            $query->whereIn('country_id', $filters['country_id']);
        }

        // Filter by certificate IDs
        if (!empty($filters['certificate_id']) && is_array($filters['certificate_id'])) {
            $query->whereHas('certificates', function ($q) use ($filters) {
                $q->whereIn('certificates.id', $filters['certificate_id']);
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }
}
