<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\InitialController;
use App\Http\Controllers\Auth\PusherController;
use App\Http\Controllers\Role\RoleListController;
use App\Http\Controllers\User\UserListController;
use App\Http\Controllers\User\UserShowController;


use App\Http\Controllers\User\UserStoreController;
use App\Http\Controllers\User\UserActiveController;
use App\Http\Controllers\User\UserByRoleController;
use App\Http\Controllers\User\UserDeleteController;

use App\Http\Controllers\User\UserUpdateController;
use App\Http\Controllers\Product\ProductEditController;
use App\Http\Controllers\Product\ProductListController;
use App\Http\Controllers\Product\ProductShowController;
use App\Http\Controllers\Product\ProductStoreController;
use App\Http\Controllers\Product\ProductDeleteController;
use App\Http\Controllers\Product\ProductUpdateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'check.status'])->get('/auth/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'check.status'])->get('/auth/current', function (Request $request) {
    return $request->user();
});


Route::group([ 'middleware' => [ 'auth:sanctum', 'check.status' ] ], function() {

    // Push auth
    Route::post('pusher', PusherController::class);

    // App 
    Route::group([ 'prefix' => 'dashboard' ], function() {
        Route::group([ 'prefix' => 'admin' ], function() {
        });

        Route::group([ 'prefix' => 'attendant' ], function() {
        });

        Route::group([ 'prefix' => 'exhibitor' ], function() {
        });

        Route::group([ 'prefix' => 'buyer' ], function() {
        });

        Route::group([ 'prefix' => 'sponsor' ], function() {
        });

        Route::group([ 'prefix' => 'speaker' ], function() {
        });
    });

    // App 
    Route::group([ 'prefix' => 'app' ], function() {
        Route::get('initial', InitialController::class);
    });

    // Products 
    Route::group([ 'prefix' => 'products' ], function() {
        Route::get('/', ProductListController::class);
        Route::post('/', ProductStoreController::class);
        Route::get('/{id}/edit', ProductEditController::class);
        Route::post('/{id}', ProductUpdateController::class);
        Route::delete('/{id}', ProductDeleteController::class);
        Route::get('/{id}', ProductShowController::class);
    });
    
    // Users 
    Route::group([ 'prefix' => 'users' ], function() {
        Route::get('/', UserListController::class);
        Route::get('/by-roles', UserByRoleController::class);
        Route::post('/', UserStoreController::class);
        Route::get('/{id}', UserShowController::class);
        Route::post('/{id}', UserUpdateController::class);
        Route::post('/{id}/active', UserActiveController::class);
        Route::delete('/{id}', UserDeleteController::class);
    });

    // Roles 
    Route::group([ 'prefix' => 'roles' ], function() {
        Route::get('/', RoleListController::class);
    });

    
    // Notifications
    Route::group([ 'prefix' => 'notifications' ], function() {
        Route::get('/', [App\Http\Controllers\API\NotificationController::class, 'index']);
        Route::get('/unread-count', [App\Http\Controllers\API\NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
        Route::post('/{notification}/mark-read', [App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
        Route::post('/{notification}/mark-unread', [App\Http\Controllers\API\NotificationController::class, 'markAsUnread']);
        Route::delete('/{notification}', [App\Http\Controllers\API\NotificationController::class, 'destroy']);
    });
    
});