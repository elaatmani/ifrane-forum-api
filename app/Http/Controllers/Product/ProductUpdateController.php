<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductUpdateController extends Controller
{
    protected $repository;

    public function __construct(ProductRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProductRequest $request, $id)
    {
        $product = $this->repository->update($id, $request->validated(), $request->variants);

        $product = $this->repository->find($id);

        $product->load('variants');
        


        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Product updated successfully',
            'product' => $product
        ], 201);
    }
}
