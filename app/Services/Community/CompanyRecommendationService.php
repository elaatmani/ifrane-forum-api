<?php

namespace App\Services\Community;

use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CompanyRecommendationService
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private CompanyDataService $companyDataService
    ) {}

    /**
     * Get company recommendations for the "You may also like" section.
     *
     * @param Company $targetCompany The company being viewed
     * @param User|null $authUser The authenticated user viewing the profile
     * @return array
     */
    public function getRecommendationsForCompany(Company $targetCompany, ?User $authUser): array
    {
        try {
            $similarCompanies = $this->getSimilarCompanies($targetCompany);
            
            if ($similarCompanies->isEmpty()) {
                return [];
            }

            return $this->transformSimilarCompaniesToRecommendations(
                $similarCompanies,
                $targetCompany->id
            );

        } catch (\Exception $e) {
            Log::warning('Failed to get company recommendations', [
                'target_company_id' => $targetCompany->id,
                'auth_user_id' => $authUser?->id,
                'error' => $e->getMessage()
            ]);
            
            // Gracefully handle any errors - don't break the main resource
            return [];
        }
    }

    /**
     * Get similar companies using the repository method.
     *
     * @param Company $targetCompany
     * @return \Illuminate\Support\Collection
     */
    private function getSimilarCompanies(Company $targetCompany)
    {
        return $this->companyRepository->getSimilarCompanies($targetCompany, [
            'limit' => 8,
            'factors' => [
                'industry_alignment' => ['enabled' => true, 'weight' => 4],
                'geographic_proximity' => ['enabled' => true, 'weight' => 2],
                'certification_similarity' => ['enabled' => true, 'weight' => 3],
                'size_similarity' => ['enabled' => true, 'weight' => 2],
                'user_role_compatibility' => ['enabled' => true, 'weight' => 3]
            ]
        ]);
    }

    /**
     * Transform similar companies into recommendation format.
     *
     * @param \Illuminate\Support\Collection $similarCompanies
     * @param int $targetCompanyId The company being viewed (to exclude)
     * @return array
     */
    private function transformSimilarCompaniesToRecommendations($similarCompanies, int $targetCompanyId): array
    {
        return $similarCompanies
            ->reject(fn($company) => $company->id === $targetCompanyId) // Extra safety check
            ->map(function($company) {
                return $this->formatRecommendationCompany($company);
            })
            ->values()
            ->toArray();
    }

    /**
     * Format a single company for recommendation output.
     *
     * @param Company $company
     * @return array
     */
    private function formatRecommendationCompany(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'country' => $company->country?->name,
            'logo' => $this->companyDataService->getCompanyLogo($company),
        ];
    }



    /**
     * Get configuration for company recommendations display.
     *
     * @return array
     */
    public function getRecommendationConfig(): array
    {
        return [
            'title' => 'You may also like',
            'description' => 'Discover other companies that might interest you based on industry, location, and business focus.',
            'max_items' => 8,
        ];
    }
} 