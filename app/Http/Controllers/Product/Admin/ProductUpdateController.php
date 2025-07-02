<?php

namespace App\Http\Controllers\Product\Admin;

use App\Http\Requests\Product\ProductUpdateRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductUpdateController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductUpdateRequest $request, $id)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_url'] = $request->file('thumbnail')->store('products', 'public');
        }

        if ($request->has('category_ids')) {
            $data['category_ids'] = json_decode($request->category_ids, true);
        }

        $product = $this->productRepository->update($id, $data);

        return response()->json([
            'code' => 'SUCCESS',
        'message' => 'Product updated successfully',
            'product' => $product
        ], 200);
    }
}
