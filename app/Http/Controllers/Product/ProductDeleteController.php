<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductDeleteController extends Controller
{
    protected $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
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

        // verify ability to delete

        $this->repository->delete($id);

        return response()->json([
            'message' => 'Product deleted successfully',
            'code' => 'SUCCESS'
        ], 200);
    }
}
