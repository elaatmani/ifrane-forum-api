<?php

namespace App\Http\Controllers\Category\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryStoreController extends Controller
{

    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $validator->errors()
            ], 422);
        }
        
        $category = $this->categoryRepository->create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'status' => $request->status,
        ]);
        
        return response()->json([
            'category' => $category,
            'status' => 'SUCCESS',
            'message' => 'Category created successfully'
        ]);
    }
}
