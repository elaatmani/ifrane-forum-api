<?php

namespace App\Http\Controllers\Analytics\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AdAnalyticsService;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Analytics\KPIsAnalyticsService;
use App\Services\Analytics\OrderAnalyticsService;
use App\Services\Analytics\ProductPerformanceService;

class AnalyticsController extends Controller
{
    public $orderAnalyticsService;
    public $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->orderAnalyticsService = new OrderAnalyticsService();
        $this->productRepository = $productRepository;
    }
    public function revenue(Request $request) {
        $params = $request->all();

        return response()->json([
            'data' => $this->orderAnalyticsService->getRevenue($params)
        ]);
    }

    public function confirmation(Request $request) {
        $params = $request->all();

        return response()->json([
            'data' => $this->orderAnalyticsService->getConfrimationCount($params),
            'params' => $params
        ]);
    }

    public function delivery(Request $request) {
        $params = $request->all();

        return response()->json([
            'data' => $this->orderAnalyticsService->getDeliveryCount($params)
        ]);
    }

    public function kpis(Request $request) {
        $params = $request->all();
        $kpis = new KPIsAnalyticsService();
        $kpis = $kpis->index($request);


        return response()->json([
            'data' => $kpis
        ]);
    }

    public function ads(Request $request) {
        $params = $request->all();
        $ads = new AdAnalyticsService();
        
        $ads = $ads->index($request);


        return response()->json([
            'data' => $ads
        ]);
    }

    public function leadsByRange(Request $request) {
        $params = $request->all();
        $leads = new AdAnalyticsService();
        $leads = $leads->leadsByRange($request);

        return response()->json([
            'data' => $leads
        ]);
    }

    public function productPerformance(Request $request) {
        $params = $request->all();
        $results = new ProductPerformanceService();
        $results = $results->index($request);


        return response()->json([
            'data' => $results
        ]);
    }

    public function products(Request $request) {
        $params = $request->all();
        $results = $this->productRepository->topProducts($params);


        return response()->json([
            'data' => $results
        ]);
    }

    public function productById(Request $request, $id) {
        $params = $request->all();
        $params['product_id'] = $id;
        
        // Get product analytics for a specific product
        $results = $this->productRepository->productAnalytics($params);

        return response()->json($results);
    }
}
