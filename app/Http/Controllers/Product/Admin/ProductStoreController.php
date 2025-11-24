<?php

namespace App\Http\Controllers\Product\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Product\Admin\ProductStoreRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Http\Resources\Product\Company\ProductListResource;

class ProductStoreController extends Controller
{

    protected $repository;

    public function __construct(ProductRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductStoreRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->user()->id;

        $companyId = $request->company_id;

        $data['company_id'] = $companyId;
        $data['name'] = $data['title'];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_url'] = $request->file('thumbnail')->store('products', 'public');
        }

        if ($request->has('category_ids')) {
            $data['category_ids'] = json_decode($request->category_ids, true);
        }

        

        $product = $this->repository->create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => new ProductListResource($product)
        ], 201);
    }
}
