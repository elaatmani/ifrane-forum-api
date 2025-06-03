<?php 


namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enums\OrderConfirmationEnum;


class SortingHelper {
    public static function order(Request $request)
    {
        $filters = $request->input('sorting', []);
        $criteria = [];
        return $criteria;
    }


    public static function getDateFromParams($params, $key)
    {
        $dateFrom = data_get($params, "$key.from");
        $dateTo = data_get($params, "$key.to");

        return [
            'from' => $dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : null,
            'to' => $dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : null,
        ];
    }

}