<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;

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

Route::get('/test', function () {
    $product = Product::latest()->first();

    $categories = $product->categories->map(function($category) {
        return ['id' => $category->id, 'name' => $category->name];
    });

    return response()->json([
        // 'product' => $product,
        'categories' => $categories
    ]);
});

// Test document upload endpoint
Route::post('/test-document-upload', function (Illuminate\Http\Request $request) {
    return response()->json([
        'message' => 'Test endpoint for document upload',
        'has_file' => $request->hasFile('file'),
        'file_info' => $request->hasFile('file') ? [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize(),
            'mime_type' => $request->file('file')->getMimeType(),
            'extension' => $request->file('file')->getClientOriginalExtension(),
        ] : null,
        'all_data' => $request->all()
    ]);
});

