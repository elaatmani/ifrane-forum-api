<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyAllListController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $companies = $this->companyRepository
        ->all();

        $companies = $companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name
            ];
        });

        return response()->json([
            'companies' => $companies
        ]);
    }
}
