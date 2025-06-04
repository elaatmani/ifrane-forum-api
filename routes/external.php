<?php

use App\Http\Controllers\External\OrderDeliveryUpdateController;
use App\Http\Controllers\External\Pusher\PresenceWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Google\GoogleSheetController;
use App\Services\NawrisService;

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

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::post('/pusher/presence', PresenceWebhookController::class);
