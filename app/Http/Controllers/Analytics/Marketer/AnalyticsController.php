<?php

namespace App\Http\Controllers\Analytics\Marketer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Analytics\MarketerAnalyticsService;

class AnalyticsController extends Controller
{
    public $marketerAnalyticsService;

    public function __construct()
    {
        $this->marketerAnalyticsService = new MarketerAnalyticsService();
    }

    
}
