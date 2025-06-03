<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ad\AdListController;
use App\Http\Controllers\Ad\AdStoreController;
use App\Http\Controllers\Ad\AdDeleteController;
use App\Http\Controllers\Ad\AdUpdateController;
use App\Http\Controllers\App\InitialController;
use App\Http\Controllers\Auth\PusherController;
use App\Http\Controllers\City\CityListController;
use App\Http\Controllers\Role\RoleListController;
use App\Http\Controllers\User\UserListController;
use App\Http\Controllers\User\UserShowController;

use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\User\UserStoreController;
use App\Http\Controllers\City\CityUpdateController;
use App\Http\Controllers\Dashboard\ChartController;
use App\Http\Controllers\Order\OrderCallController;

use App\Http\Controllers\Order\OrderListController;
use App\Http\Controllers\Order\OrderShowController;
use App\Http\Controllers\User\UserActiveController;
use App\Http\Controllers\User\UserByRoleController;
use App\Http\Controllers\User\UserDeleteController;

use App\Http\Controllers\User\UserUpdateController;
use App\Http\Controllers\Order\OrderStoreController;
use App\Http\Controllers\Order\OrderDeleteController;
use App\Http\Controllers\Order\OrderUpdateController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Order\OrderHistoryController;
use App\Http\Controllers\Product\ProductEditController;
use App\Http\Controllers\Product\ProductListController;
use App\Http\Controllers\Product\ProductShowController;
use App\Http\Controllers\Search\GlobalSearchController;
use App\Http\Controllers\Product\ProductStoreController;
use App\Http\Controllers\User\UserTrackedTimeController;
use App\Http\Controllers\Product\ProductDeleteController;
use App\Http\Controllers\Product\ProductUpdateController;
use App\Http\Controllers\Sourcing\SourcingListController;
use App\Http\Controllers\Sourcing\SourcingShowController;
use App\Http\Controllers\Sourcing\SourcingStoreController;
use App\Http\Controllers\Product\ProductForOrderController;
use App\Http\Controllers\Sourcing\SourcingDeleteController;
use App\Http\Controllers\Sourcing\SourcingUpdateController;
use App\Http\Controllers\Analytics\Admin\AnalyticsController;
use App\Http\Controllers\Dashboard\AgentDashboardController;
use App\Http\Controllers\Order\OrderResponsibilityController;
use App\Http\Controllers\GoogleSheet\GoogleSheetListController;
use App\Http\Controllers\GoogleSheet\GoogleSheetShowController;
use App\Http\Controllers\GoogleSheet\GoogleSheetStoreController;
use App\Http\Controllers\GoogleSheet\GoogleSheetDeleteController;
use App\Http\Controllers\GoogleSheet\GoogleSheetUpdateController;
use App\Http\Controllers\GoogleSheet\GoogleSheetRefreshController;
use App\Http\Controllers\Sourcing\SourcingArrivedController;

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
            Route::get('agents', [UserController::class, 'agents']);
            Route::get('followups', [UserController::class, 'followups']);
            Route::get('overview', [OverviewController::class, 'admin']);
            Route::get('daily-new-orders', [ChartController::class, 'getDailyNewOrders']);
            Route::get('daily-confirmed-orders', [ChartController::class, 'getDailyConfirmedOrders']);
            Route::get('daily-delivered-orders', [ChartController::class, 'getDailyDeliveredOrders']);
        });

        Route::group([ 'prefix' => 'agent' ], function() {
            Route::get('performance', [AgentDashboardController::class, 'performance']);
            Route::get('daily-dropped-orders', [AgentDashboardController::class, 'getDailyDroppedOrders']);
            Route::get('daily-treated-orders', [AgentDashboardController::class, 'getDailyTreatedOrders']);
            Route::get('daily-confirmed-orders', [AgentDashboardController::class, 'getDailyConfirmedOrders']);
            Route::get('daily-delivered-orders', [AgentDashboardController::class, 'getDailyDeliveredOrders']);
            Route::get('top-products', [AgentDashboardController::class, 'topProductConfirmation']);
        });
    });

    // App 
    Route::group([ 'prefix' => 'app' ], function() {
        Route::get('initial', InitialController::class);
    });

    // Products 
    Route::group([ 'prefix' => 'products' ], function() {
        Route::get('/', ProductListController::class);
        Route::get('/for-orders', ProductForOrderController::class);
        Route::post('/', ProductStoreController::class);
        Route::get('/offers', [ProductForOrderController::class, 'offers']);
        Route::get('/cross', [ProductForOrderController::class, 'cross_products']);
        Route::get('/{id}/edit', ProductEditController::class);
        Route::post('/{id}', ProductUpdateController::class);
        Route::delete('/{id}', ProductDeleteController::class);
        Route::get('/{id}', ProductShowController::class);
        Route::get('/{id}/agents', [ProductShowController::class, 'agents']);
        Route::get('/{id}/agents-by-range', [ProductShowController::class, 'agents_by_range']);
        Route::get('/{id}/marketers', [ProductShowController::class, 'marketers']);
        Route::get('/{id}/marketers-by-range', [ProductShowController::class, 'marketers_by_range']);
        Route::get('/{id}/status', [ProductShowController::class, 'product_status']);
        Route::get('/{id}/inventory', [ProductShowController::class, 'inventory']);
    });

    // Sheets 
    Route::group([ 'prefix' => 'sheets' ], function() {
        Route::get('/', GoogleSheetListController::class);
        Route::post('/', GoogleSheetStoreController::class);
        Route::get('/{id}', GoogleSheetShowController::class);
        Route::post('/{id}', GoogleSheetUpdateController::class);
        Route::delete('/{id}', GoogleSheetDeleteController::class);
        Route::get('/{id}/sync', GoogleSheetRefreshController::class);
    });
    
    // Sourcing 
    Route::group([ 'prefix' => 'sourcings' ], function() {
        Route::get('/', SourcingListController::class);
        Route::post('/', SourcingStoreController::class);
        Route::get('/{id}', SourcingShowController::class);
        Route::post('/{id}', SourcingUpdateController::class);
        Route::delete('/{id}', SourcingDeleteController::class);
        Route::post('/webhook/arrived', SourcingArrivedController::class);
    });
    
    // Users 
    Route::group([ 'prefix' => 'users' ], function() {
        Route::get('/', UserListController::class);
        Route::get('/by-roles', UserByRoleController::class);
        Route::get('/{id}/sessions/{date}', UserTrackedTimeController::class);
        Route::get('/{id}/sessions', [UserTrackedTimeController::class, 'byDays']);
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

    // Orders 
    Route::group([ 'prefix' => 'orders' ], function() {
        Route::get('/', OrderListController::class);
        Route::get('/handle', OrderResponsibilityController::class);
        Route::post('/', OrderStoreController::class);
        Route::get('/cancellation-reasons', App\Http\Controllers\Order\OrderCancellationReasonsController::class);
        Route::get('/{id}', OrderShowController::class);
        Route::post('/{id}', OrderUpdateController::class);
        Route::post('/{id}/calls', OrderCallController::class);
        Route::get('/{id}/history', OrderHistoryController::class);
        Route::delete('/{id}', OrderDeleteController::class);
    });

    // Ads 
    Route::group([ 'prefix' => 'ads' ], function() {
        Route::get('/', AdListController::class);
        Route::post('/', AdStoreController::class);
        Route::post('/{id}', AdUpdateController::class);
        Route::delete('/{id}', AdDeleteController::class);
    });

    Route::group([ 'prefix' => 'cities' ], function() {
        Route::get('/', CityListController::class);
        Route::post('/{id}', CityUpdateController::class);
    });

    // Search 
    Route::group([ 'prefix' => 'search' ], function() {
        Route::get('/', GlobalSearchController::class);
    });

    // Analytics 
    Route::group([ 'prefix' => 'analytics' ], function() {
        Route::get('/confirmation', [AnalyticsController::class, 'confirmation']);
        Route::get('/delivery', [AnalyticsController::class, 'delivery']);
        Route::get('/revenue', [AnalyticsController::class, 'revenue']);
        Route::get('/kpis', [AnalyticsController::class, 'kpis']);
        Route::get('/ads', [AnalyticsController::class, 'ads']);
        Route::get('/leads-by-range', [AnalyticsController::class, 'leadsByRange']);
        Route::get('/product-performance', [AnalyticsController::class, 'productPerformance']);
        Route::get('/products', [AnalyticsController::class, 'products']);
        Route::get('/products/{id}', [AnalyticsController::class, 'productById']);
        
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
    
    // Test endpoints
    Route::group([ 'prefix' => 'test' ], function() {
        Route::post('/notification', [App\Http\Controllers\API\TestController::class, 'createNotification']);
    });
    
});