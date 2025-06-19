<?php

namespace App\Http\Controllers\Category\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryListController extends Controller
{

    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $categories = $this->categoryRepository->query()
        ->whereNull('deleted_at')
        ->when(!empty($request->search), function($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->when(!empty($request->type), function($query) use ($request) {
            $query->where('type', $request->type);
        })
        ->paginate($request->per_page ?? 10);

        return response()->json($categories);
    }
}
