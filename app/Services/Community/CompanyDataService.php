<?php

namespace App\Services\Community;

use App\Models\Company;

class CompanyDataService
{
    /**
     * Format company data for API responses.
     *
     * @param Company|null $company
     * @return array|null
     */
    public function formatCompanyData(?Company $company): ?array
    {
        if (!$company) {
            return null;
        }

        return [
            'id' => $company->id,
            'name' => $company->name,
            'logo' => $this->getCompanyLogo($company),
        ];
    }

    /**
     * Get formatted company logo URL.
     *
     * @param Company|null $company
     * @return string|null
     */
    public function getCompanyLogo(?Company $company): ?string
    {
        if (!$company || !$company->logo) {
            return null;
        }

        return asset('storage/' . $company->logo);
    }

    /**
     * Format company data for recommendation items.
     *
     * @param Company|null $company
     * @return array|null
     */
    public function formatRecommendationCompanyData(?Company $company): ?array
    {
        // Same format as main company data for consistency
        return $this->formatCompanyData($company);
    }
} 