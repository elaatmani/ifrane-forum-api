<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyShowResource;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyShowController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $company = $this->companyRepository->find($request->id);

        return response()->json(
            [
                'code' => 'SUCCESS',
                'data' => new CompanyShowResource($company),
            ]
        );
    }

    public function getCompanyServices(Request $request)
    {
        $company = $this->companyRepository->find($request->id);

        $services = $this->companyRepository->getCompanyServices($company);

        return response()->json(
            [
                'code' => 'SUCCESS',
                'data' => $services,
            ]
        );
    }


    public function getCompanyProducts(Request $request)
    {
        $company = $this->companyRepository->find($request->id);

        $products = $this->companyRepository->getCompanyProducts($company);

        return response()->json(
            [
                'code' => 'SUCCESS',
                'data' => $products,
            ]
        );
    }
}
