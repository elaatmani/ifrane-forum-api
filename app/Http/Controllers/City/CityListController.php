<?php

namespace App\Http\Controllers\City;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $cities = City::query()
        ->paginate($request->get('per_page', 10));

        return response()->json([
            'cities' => $cities
        ]);
    }
}
