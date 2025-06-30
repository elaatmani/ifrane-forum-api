<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductEditController extends Controller
{

    protected $repository;

    public function __construct(ProductRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $product = $this->repository->find($id);

        if(!$product) {
            return response()->json([
                'message' => 'Product not found',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        $thumbnail_url = $product->thumbnail_url ? asset('storage/' . $product->thumbnail_url) : null;

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'thumbnail_url' => $thumbnail_url,
            'categories' => $product->categories->map(function($category) {
                return ['id' => $category->id, 'name' => $category->name];
            }),
        ]);
    }
}
