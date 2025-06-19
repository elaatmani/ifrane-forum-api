<?php

namespace App\Repositories\Contracts;

interface CompanyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Delete company files from storage
     */
    public function deleteCompanyFiles($company): void;

    /**
     * Get filtered and paginated companies
     */
    public function getFilteredCompanies(array $filters, int $perPage = 10);
}
