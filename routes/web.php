<?php

use App\Services\NawrisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Google\GoogleSheetController;
use App\Http\Controllers\Search\GlobalSearchController;
use App\Http\Controllers\GoogleSheet\GoogleSheetRefreshController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


require __DIR__.'/auth.php';
