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
                'logo' => $company->logo ? asset('storage/' . $company->logo) : null,
                'background' => $company->background_image ? asset('storage/' . $company->background_image) : null,
                'created_at' => $company->created_at
            ];
        });

        return response()->json([
            'companies' => $companies
        ]);
    }
}
