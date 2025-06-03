<?php

namespace App\Repositories\Contracts;

interface AdRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get ads with optimized query for performance
     * 
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAdsWithResultsOptimized(array $filters, int $perPage = 10);

    /**
     * Get KPIs with optimized query
     * 
     * @param array $filters
     * @return object
     */
    public function getKpisOptimized(array $filters);
}
