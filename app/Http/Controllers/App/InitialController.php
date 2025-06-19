<?php

namespace App\Http\Controllers\App;

use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\City\CityResource;
use App\Http\Resources\User\UserDeliveryResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Http\Resources\Product\ProductForOrderCollection;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;

class InitialController extends Controller
{


    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected CertificateRepositoryInterface $certificateRepository,
        protected CountryRepositoryInterface $countryRepository
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $response = [
            'role' => auth()->user()->roles->first()->name
        ];
        switch (auth()->user()->roles->first()->name) {
            case 'admin':
                $result =  $this->admin();
                break;
            default:
                $result = [];
            break;
        }

        return response()->json(array_merge($response, $result), 200);
    }

    public function admin()
    {
        // $countries = $this->countryRepository->query()->select('id', 'name')->get();
        // $categories = $this->categoryRepository->query()->select('id', 'name')->get();
        // $certificates = $this->certificateRepository->query()->select('id', 'name')->get();
        // $users = $this->userRepository->query()->select('id', 'name')->get();
        
        return [
            'code' => 'SUCCESS',
        ];
    }

}
