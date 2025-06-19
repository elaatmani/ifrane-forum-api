<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyListResource;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyListController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Prepare filters array
        $filters = [];
        
        // Handle search parameter
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }
        
        // Handle category_id array
        if ($request->has('category_id') && is_array($request->category_id)) {
            $filters['category_id'] = array_filter($request->category_id, 'is_numeric');
        }
        
        // Handle country_id array
        if ($request->has('country_id') && is_array($request->country_id)) {
            $filters['country_id'] = array_filter($request->country_id, 'is_numeric');
        }
        
        // Handle certificate_id array
        if ($request->has('certificate_id') && is_array($request->certificate_id)) {
            $filters['certificate_id'] = array_filter($request->certificate_id, 'is_numeric');
        }

        // Get per_page parameter
        $perPage = $request->per_page ?? 10;

        // Get filtered companies
        $companies = $this->companyRepository->getFilteredCompanies($filters, $perPage);

        // Transform the collection
        $companies->getCollection()->transform(fn($company) => new CompanyListResource($company));

        return response()->json($companies);
    }
}
