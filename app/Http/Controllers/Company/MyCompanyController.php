<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Http\Request;

class MyCompanyController extends Controller
{
    protected $repository;

    public function __construct(CompanyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $companies = $this->repository->getCompaniesByUserId($user->id);

        $companies = $companies->map(function($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'description' => $company->description,
                'logo' => $company->logo_url ? asset('storage/' . $company->logo_url) : null,
                'background' => $company->background_url ? asset('storage/' . $company->background_url) : null,
                'created_at' => $company->created_at
            ];
        });

        return response()->json([
            'companies' => $companies
        ]);
    }
}
