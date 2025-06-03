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

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/search', GlobalSearchController::class);

Route::get('/debug', function () {
    return response()->json([
        'data' => NawrisService::areas(4)['feed']
    ]);
});

Route::get('/fetch', [GoogleSheetController::class, 'syncSheet']);
Route::get('/sheets/sync', [GoogleSheetController::class, 'sync'])->name('sheets.sync');

Route::get('/test', function () {
    
    $product_id = 1;
    
    $product = DB::table('products')->where('id', $product_id)->first();
    
    // Alternative using join
    $orders = DB::table('orders as o')
    ->join('order_items as oi', 'o.id', 'oi.order_id')
    ->where('oi.product_id', $product_id)
    ->whereNull('o.deleted_at')
    ->whereNull('oi.deleted_at')
    ->select(
            DB::raw('COUNT(*) AS total_orders'),
            DB::raw('SUM(CASE WHEN o.agent_status IN ("confirmed") THEN 1 ELSE 0 END) AS confirmed_orders'),
            DB::raw('SUM(CASE WHEN o.delivery_status IN ("delivered", "settled") THEN 1 ELSE 0 END) AS delivered_orders'),
        )->get();

    
    $shipping = DB::table('orders as o')
    ->join('cities as c', 'o.customer_city', 'c.name')
    ->whereIn('o.delivery_status', ['delivered', 'settled'])
    ->whereNull('o.deleted_at')
    ->whereExists(function ($query) use ($product_id) {
        $query->select(DB::raw(1))
            ->from('order_items as oi')
            ->whereColumn('o.id', 'oi.order_id')
            ->where('oi.product_id', $product_id)
            ->whereNull('oi.deleted_at');
    })
    ->sum('c.shipping_cost');
    
    $order_items = DB::table('order_items as oi')
    ->join('orders as o', 'o.id', 'oi.order_id')
    ->whereIn('o.delivery_status', ['delivered', 'settled'])
    ->where('o.agent_status', 'confirmed')
    ->whereNull('oi.deleted_at')
    ->whereNull('o.deleted_at')
    ->where('oi.product_id', $product_id)->sum('price');
    
    $product_cost = DB::table('order_items as oi')
    ->join('orders as o', 'o.id', 'oi.order_id')
    ->join('products as p', 'oi.product_id', 'p.id')
    ->whereIn('o.delivery_status', ['delivered', 'settled'])
    ->where('oi.product_id', $product_id)
    ->whereNull('oi.deleted_at')
    ->whereNull('o.deleted_at')
    ->sum(DB::raw('p.buying_price * oi.quantity'));
    
    $total_spend = DB::table('ads')->where('product_id', $product_id)->sum('spend');
    
    return response()->json([
        'product' => $product,
        'order' => $orders,
        'chiffre' => $order_items / 4.88,
        'total_spend' => $total_spend,
        'shipping' => (float) $shipping / 4.88,
        'product_cost' => (float) $product_cost
    ]);
});


require __DIR__.'/auth.php';
