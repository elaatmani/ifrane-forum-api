<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;

class MarketerAnalyticsService
{

    public function index(Request $request)
    {
    }


    public static function parseDateRange($key)
    {
        $fromDate = data_get(request()->all(), $key . '.from', null);
        $toDate = data_get(request()->all(), $key . '.to', null);

        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

}
