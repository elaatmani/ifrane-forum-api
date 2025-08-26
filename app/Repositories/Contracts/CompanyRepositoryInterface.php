<?php

namespace App\Repositories\Contracts;

use App\Models\Company;

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

    public function getCompaniesByUserId($userId);

    /**
     * Get similar companies for a company based on configurable recommendation factors
     *
     * @param Company $company The company to get recommendations for
     * @param array $params Optional parameters including factor configuration, limits, etc.
     * @param bool $get Whether to execute the query (true) or return collection (false)
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getSimilarCompanies(Company $company, array $params = [], bool $get = true);

    public function getCompanyServices(Company $company);

    public function getCompanyProducts(Company $company);
}
