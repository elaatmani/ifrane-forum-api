<?php

namespace App\Http\Controllers\Company\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Http\Resources\Company\Admin\CompanyEditResource;

class CompanyEditController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        // Find the company with all relationships loaded
        $company = $this->companyRepository->query()
            ->with(['users', 'categories', 'certificates', 'country'])
            ->find($id);

        if (!$company) {
            return response()->json([
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json(new CompanyEditResource($company) );
    }
}
