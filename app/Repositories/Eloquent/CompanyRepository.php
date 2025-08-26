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

    public function getCompaniesByUserId($userId)
    {
        return $this->model->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        })->get();
    }

    /**
     * Get similar companies for a company based on configurable recommendation factors
     * 
     * This method implements a scoring system with 5 configurable factors:
     * 1. Industry Alignment - Companies in same/similar categories
     * 2. Geographic Proximity - Companies in same country/region  
     * 3. Certification Similarity - Companies with similar certificates
     * 4. Size Similarity - Companies with similar number of users/employees
     * 5. User Role Compatibility - Companies with users in compatible business roles
     * 
     * @param Company $company The company to get recommendations for
     * @param array $params Configuration parameters including factors, limits, etc.
     * @param bool $get Whether to execute the query (true) or return collection (false)
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     * 
     * Example usage:
     * // Use default factors
     * $similarCompanies = $companyRepository->getSimilarCompanies($company);
     * 
     * // Custom factor configuration
     * $similarCompanies = $companyRepository->getSimilarCompanies($company, [
     *     'limit' => 8,
     *     'factors' => [
     *         'industry_alignment' => ['enabled' => true, 'weight' => 4],
     *         'geographic_proximity' => ['enabled' => true, 'weight' => 2],
     *         'certification_similarity' => ['enabled' => true, 'weight' => 3],
     *         'size_similarity' => ['enabled' => true, 'weight' => 2],
     *         'user_role_compatibility' => ['enabled' => true, 'weight' => 3]
     *     ]
     * ]);
     */
    public function getSimilarCompanies(Company $company, array $params = [], bool $get = true)
    {
        // Default configuration for company recommendations
        $defaultFactors = [
            'industry_alignment' => ['enabled' => true, 'weight' => 4],
            'geographic_proximity' => ['enabled' => true, 'weight' => 2],
            'certification_similarity' => ['enabled' => true, 'weight' => 3],
            'size_similarity' => ['enabled' => true, 'weight' => 2],
            'user_role_compatibility' => ['enabled' => true, 'weight' => 3]
        ];

        $factorParams = $params['factors'] ?? [];
        
        // Merge default factors with provided factor overrides
        $factors = [];
        foreach ($defaultFactors as $factorName => $defaultSettings) {
            $factors[$factorName] = array_merge(
                $defaultSettings, 
                $factorParams[$factorName] ?? []
            );
        }

        // Get limits and filtering settings
        $limit = $params['limit'] ?? 8;
        $minScore = $params['min_score'] ?? 3;
        $maxLimit = 50;
        
        // Ensure limit doesn't exceed maximum
        $limit = min($limit, $maxLimit);

        // Get base query for potential candidates
        $candidatesQuery = $this->model->with(['categories', 'certificates', 'country', 'users.roles'])
            ->where('id', '!=', $company->id);

        // Apply basic filtering
        // Could add more filters here like active status, etc.

        $candidates = $candidatesQuery->get();

        // If no candidates found, return empty collection
        if ($candidates->isEmpty()) {
            return $get ? collect([]) : collect([]);
        }

        // Calculate scores for each candidate
        $scoredCandidates = $candidates->map(function ($candidate) use ($company, $factors) {
            $score = $this->calculateCompanySimilarityScore($company, $candidate, $factors);
            $candidate->similarity_score = $score;
            return $candidate;
        });

        // Filter by minimum score and sort by score descending
        $recommendedCompanies = $scoredCandidates
            ->filter(fn($candidate) => $candidate->similarity_score >= $minScore)
            ->sortByDesc('similarity_score')
            ->take($limit)
            ->values();

        return $get ? $recommendedCompanies : $recommendedCompanies;
    }

    /**
     * Calculate similarity score between two companies based on enabled factors
     * 
     * @param Company $company The source company
     * @param Company $candidate The candidate company to score
     * @param array $factors Factor configuration with enabled/weight settings
     * @return int Total similarity score
     */
    private function calculateCompanySimilarityScore(Company $company, Company $candidate, array $factors): int
    {
        $totalScore = 0;

        // Factor 1: Industry Alignment
        if ($factors['industry_alignment']['enabled'] ?? false) {
            $industryScore = $this->calculateIndustryAlignmentScore($company, $candidate);
            $weight = $factors['industry_alignment']['weight'] ?? 1;
            $totalScore += $industryScore * $weight;
        }

        // Factor 2: Geographic Proximity  
        if ($factors['geographic_proximity']['enabled'] ?? false) {
            $geoScore = $this->calculateGeographicProximityScore($company, $candidate);
            $weight = $factors['geographic_proximity']['weight'] ?? 1;
            $totalScore += $geoScore * $weight;
        }

        // Factor 3: Certification Similarity
        if ($factors['certification_similarity']['enabled'] ?? false) {
            $certScore = $this->calculateCertificationSimilarityScore($company, $candidate);
            $weight = $factors['certification_similarity']['weight'] ?? 1;
            $totalScore += $certScore * $weight;
        }

        // Factor 4: Size Similarity
        if ($factors['size_similarity']['enabled'] ?? false) {
            $sizeScore = $this->calculateSizeSimilarityScore($company, $candidate);
            $weight = $factors['size_similarity']['weight'] ?? 1;
            $totalScore += $sizeScore * $weight;
        }

        // Factor 5: User Role Compatibility
        if ($factors['user_role_compatibility']['enabled'] ?? false) {
            $roleScore = $this->calculateUserRoleCompatibilityScore($company, $candidate);
            $weight = $factors['user_role_compatibility']['weight'] ?? 1;
            $totalScore += $roleScore * $weight;
        }

        return $totalScore;
    }

    /**
     * Calculate industry alignment score based on shared categories
     */
    private function calculateIndustryAlignmentScore(Company $company, Company $candidate): int
    {
        $companyCategoryIds = $company->categories->pluck('id');
        $candidateCategoryIds = $candidate->categories->pluck('id');
        
        if ($companyCategoryIds->isEmpty() || $candidateCategoryIds->isEmpty()) {
            return 0;
        }

        // Count matching categories
        $commonCategories = $companyCategoryIds->intersect($candidateCategoryIds);
        $matchCount = $commonCategories->count();
        
        if ($matchCount > 0) {
            $baseScore = 3; // Base points for industry match
            return $baseScore * $matchCount;
        }

        return 0;
    }

    /**
     * Calculate geographic proximity score based on same country
     */
    private function calculateGeographicProximityScore(Company $company, Company $candidate): int
    {
        if (!$company->country_id || !$candidate->country_id) {
            return 0;
        }

        if ($company->country_id === $candidate->country_id) {
            return 3; // Points for same country
        }

        return 0;
    }

    /**
     * Calculate certification similarity score based on shared certificates
     */
    private function calculateCertificationSimilarityScore(Company $company, Company $candidate): int
    {
        $companyCertIds = $company->certificates->pluck('id');
        $candidateCertIds = $candidate->certificates->pluck('id');
        
        if ($companyCertIds->isEmpty() || $candidateCertIds->isEmpty()) {
            return 0;
        }

        // Count matching certificates
        $commonCertificates = $companyCertIds->intersect($candidateCertIds);
        $matchCount = $commonCertificates->count();
        
        if ($matchCount > 0) {
            $baseScore = 2; // Base points for certification match
            return $baseScore * $matchCount;
        }

        return 0;
    }

    /**
     * Calculate size similarity score based on number of users
     */
    private function calculateSizeSimilarityScore(Company $company, Company $candidate): int
    {
        $companySize = $company->users->count();
        $candidateSize = $candidate->users->count();
        
        // Define size ranges for similarity
        $sizeDifference = abs($companySize - $candidateSize);
        
        if ($sizeDifference === 0) {
            return 3; // Exact same size
        } elseif ($sizeDifference <= 2) {
            return 2; // Very similar size
        } elseif ($sizeDifference <= 5) {
            return 1; // Somewhat similar size
        }

        return 0;
    }

    /**
     * Calculate user role compatibility score based on business relationships
     */
    private function calculateUserRoleCompatibilityScore(Company $company, Company $candidate): int
    {
        $companyRoles = $company->users->pluck('roles')->flatten()->pluck('name')->unique();
        $candidateRoles = $candidate->users->pluck('roles')->flatten()->pluck('name')->unique();
        
        if ($companyRoles->isEmpty() || $candidateRoles->isEmpty()) {
            return 0;
        }

        // Define compatible role combinations for business relationships
        $roleCompatibility = [
            'exhibitor' => ['buyer', 'attendee', 'sponsor'],
            'buyer' => ['exhibitor', 'speaker'],
            'speaker' => ['attendee', 'sponsor', 'exhibitor'],
            'sponsor' => ['exhibitor', 'speaker', 'attendee'],
            'attendee' => ['speaker', 'exhibitor'],
        ];

        $score = 0;
        
        foreach ($companyRoles as $companyRole) {
            $compatibleRoles = $roleCompatibility[$companyRole] ?? [];
            
            foreach ($candidateRoles as $candidateRole) {
                if (in_array($candidateRole, $compatibleRoles)) {
                    $score += 2; // Points for compatible business roles
                }
            }
        }

        return min($score, 6); // Cap the maximum score to prevent over-weighting
    }

    public function getCompanyServices(Company $company)
    {
        $services = $company->services()
        ->limit(10)
        ->where('status', 'active')
        ->select('id', 'name', 'description', 'image')
        ->get();

        // Transform image URLs to proper storage asset URLs
        return $services->map(function ($service) {
            if ($service->image) {
                $service->image = asset('storage/' . $service->image);
            }
            return $service;
        });
    }

    public function getCompanyProducts(Company $company)
    {
        $products = $company->products()
        ->limit(10)
        // ->where('status', 'active')
        ->select('id', 'name', 'description', 'thumbnail_url')
        ->get();

        // Transform thumbnail URLs to proper storage asset URLs
        return $products->map(function ($product) {
            if ($product->thumbnail_url) {
                $product->thumbnail_url = asset('storage/' . $product->thumbnail_url);
            }
            return $product;
        });
    }
}
