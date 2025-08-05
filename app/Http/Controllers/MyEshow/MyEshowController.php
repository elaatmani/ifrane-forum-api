<?php

namespace App\Http\Controllers\MyEshow;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyListResource;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\Service\ServiceListResource;
use App\Http\Resources\Session\SessionListResource;

class MyEshowController extends Controller
{

    public function __construct() {
    }

    public function myConnections(Request $request) {
        $user = auth()->user();

        $connections = $user->connections()->paginate($request->per_page ?? 10);

        // $connections->getCollection()->transform(fn($connection) => new ConnectionListResource($connection));

        return response()->json($connections);
    }

    public function mySessions() {

    }

    public function myBookmarkedCompanies(Request $request) {
        $user = auth()->user();

        $companies = $user->bookmarkedCompanyModels()->paginate($request->per_page ?? 10);

        $companies->getCollection()->transform(fn($company) => new CompanyListResource($company));

        return response()->json($companies);
    }

    public function myBookmarkedProducts(Request $request) {
        $user = auth()->user();

        $products = $user->bookmarkedProductModels()->paginate($request->per_page ?? 10);

        $products->getCollection()->transform(fn($product) => new ProductListResource($product));

        return response()->json($products);
    }

    public function myBookmarkedServices(Request $request) {
        $user = auth()->user();

        $services = $user->bookmarkedServiceModels()->paginate($request->per_page ?? 10);

        $services->getCollection()->transform(fn($service) => new ServiceListResource($service));

        return response()->json($services);
    }

    public function myBookmarkedSessions(Request $request) {
        $user = auth()->user();

        $sessions = $user->bookmarkedSessionModels()->paginate($request->per_page ?? 10);

        $sessions->getCollection()->transform(fn($session) => new SessionListResource($session));

        return response()->json($sessions);
    }

    
}
