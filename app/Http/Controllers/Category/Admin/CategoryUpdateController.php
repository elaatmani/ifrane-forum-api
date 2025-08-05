<?php

namespace App\Http\Controllers\Category\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryUpdateController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'status' => $request->status,
        ]);

        return response()->json([
            'category' => $category,
            'status' => 'SUCCESS',
            'message' => 'Category updated successfully'
        ]);
    }
}
