<?php

namespace App\Http\Controllers\City;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {

        $city = City::where('id', $id)->first();

        if(!$city) {
            return response()->json([
                'message' => 'City not found'
            ], 404);
        }

        $city->update($request->all());



        return response()->json([
            'code' => 'SUCCESS',
            'city' => $city
        ]);
    }
}
