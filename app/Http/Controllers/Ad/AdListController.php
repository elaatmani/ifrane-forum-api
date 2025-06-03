<?php

namespace App\Http\Controllers\Ad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ad\AdListResource;
use App\Repositories\Contracts\AdRepositoryInterface;

class AdListController extends Controller
{
    protected $repository;

    public function __construct(AdRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        // Prepare filters array
        $filters = $this->prepareFilters($request);
        
        // Get KPIs - these can be cached independently of pagination
        $kpisCacheKey = 'ads_kpis_' . md5(json_encode($filters));
        $kpis = Cache::remember($kpisCacheKey, 300, function() use ($filters) {
            return $this->repository->getKpisOptimized($filters);
        });
        
        // For ads list, include pagination in the cache key
        $adsCacheKey = 'ads_list_page_' . $page . '_perpage_' . $per_page . '_' . md5(json_encode($filters));
        
        // Only cache the ads data for a shorter period if paginated
        $ads = Cache::remember($adsCacheKey, 60, function() use ($filters, $per_page, $page) {
            return $this->repository->getAdsWithResultsOptimized($filters, $per_page, $page);
        });
        
        // Transform the collection - don't cache the transformed data
        $ads->setCollection(
            $ads->getCollection()->map(fn($ad) => new AdListResource($ad))
        );

        return response()->json([
            'data' => $ads,
            'kpis' => $kpis,
            'code' => 'SUCCESS',
        ]);
    }
    
    /**
     * Prepare standardized filters from request
     * 
     * @param Request $request
     * @return array
     */
    protected function prepareFilters(Request $request): array
    {
        $filters = [];
        
        // Date filters
        $filters['from'] = $request->input('filters.spent_in.from', null);
        $filters['to'] = $request->input('filters.spent_in.to', null);
        
        // Product filters
        if ($request->has('filters.product_id')) {
            $filters['product_id'] = $request->input('filters.product_id');
        }
        
        // Marketer filters
        if ($request->has('filters.marketer_id')) {
            $filters['marketer_id'] = $request->input('filters.marketer_id');
        }
        
        // Apply user restrictions
        if (!auth()->user()->hasRole('admin')) {
            $filters['user_id'] = auth()->id();
        }
        
        return $filters;
    }
}
