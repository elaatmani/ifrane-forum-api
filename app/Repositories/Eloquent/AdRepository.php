<?php

namespace App\Repositories\Eloquent;

use App\Models\Ad;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\AdRepositoryInterface;

class AdRepository extends BaseRepository implements AdRepositoryInterface
{
    public function __construct(Ad $model)
    {
        parent::__construct($model);
    }

    public function create(array $data) {
        
        $leads = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('google_sheets', 'orders.google_sheet_id', '=', 'google_sheets.id')
            ->where('order_items.product_id', $data['product_id'])
            ->where('orders.google_sheet_order_date', 'like', $data['spent_in'] . '%')
            ->where('google_sheets.marketer_id', $data['user_id'])
            ->count();

        $data['leads'] = $leads;

        return parent::create($data);
    }
    
    /**
     * Get ads with optimized query for performance
     * 
     * @param array $filters
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAdsWithResultsOptimized(array $filters, int $perPage = 10, int $page = 1)
    {
        $query = $this->query();
        
        // Add the optimized results count with marketer filter
        $query->select('ads.*');
            // ->selectSub(function ($subquery) use ($filters) {
            //     $subquery->from('orders')
            //         ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            //         // Join google_sheets to filter by marketer
            //         ->join('google_sheets', 'orders.google_sheet_id', '=', 'google_sheets.id')
            //         ->whereRaw('DATE(ads.spent_in) = DATE(orders.google_sheet_order_date)')
            //         ->whereRaw('ads.user_id = google_sheets.marketer_id')
            //         ->whereRaw('order_items.product_id = ads.product_id');
                
            //     // Apply marketer filter to the orders if needed
            //     if (!empty($filters['marketer_id'])) {
            //         $subquery->whereIn('google_sheets.marketer_id', (array)$filters['marketer_id']);
            //     }
                
            //     $subquery->selectRaw('COUNT(*)');
            // }, 'results');
        
        // Apply standard filters
        $this->applyStandardFilters($query, $filters);
        
        return $query->orderBy('id', 'DESC')->paginate($perPage, ['*'], 'page', $page);
    }
    
    /**
     * Get KPIs with optimized query
     * 
     * @param array $filters
     * @return object
     */
    public function getKpisOptimized(array $filters)
    {
        $query = $this->query();
        
        // Apply standard filters
        $this->applyStandardFilters($query, $filters);
        
        // Build the subquery SQL with marketer filter
        // $resultsSql = 'COALESCE(SUM(
        //     (SELECT COUNT(*) 
        //     FROM orders 
        //     JOIN order_items ON orders.id = order_items.order_id
        //     JOIN google_sheets ON orders.google_sheet_id = google_sheets.id
        //     WHERE DATE(ads.spent_in) = DATE(orders.google_sheet_order_date) 
        //     AND ads.user_id = google_sheets.marketer_id
        //     AND order_items.product_id = ads.product_id';
            
        // // Add the marketer filter if needed
        // if (!empty($filters['marketer_id'])) {
        //     $marketers = implode(',', array_map('intval', (array)$filters['marketer_id']));
        //     $resultsSql .= " AND google_sheets.marketer_id IN ($marketers)";
        // }
        
        // $resultsSql .= ')
        // ), 0)';
        
        return $query
            ->select(DB::raw('COALESCE(SUM(ads.spend), 0) as spend'))
            ->selectRaw("COALESCE(SUM(ads.leads), 0) as results")
            ->first();
    }
    
    /**
     * Apply standard filters to a query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyStandardFilters($query, array $filters)
    {
        // Filter by marketer ID (for ads attribution)
        if (!empty($filters['marketer_id'])) {
            $query->whereIn('user_id', (array)$filters['marketer_id']);
        }
        
        // Filter by user ID
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        // Filter by product ID
        if (!empty($filters['product_id'])) {
            $query->whereIn('product_id', (array)$filters['product_id']);
        }
        
        // Filter by date range
        if (!empty($filters['from'])) {
            $query->whereDate('spent_in', '>=', $filters['from']);
        }
        
        if (!empty($filters['to'])) {
            $query->whereDate('spent_in', '<=', $filters['to']);
        }
    }
}
