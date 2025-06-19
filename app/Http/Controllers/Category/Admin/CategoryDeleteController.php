<?php

namespace App\Http\Controllers\Category\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryDeleteController extends Controller
{

    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
    }
}
