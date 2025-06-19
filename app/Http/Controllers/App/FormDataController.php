<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;

class FormDataController extends Controller
{
    public function __construct(
        protected CountryRepositoryInterface $countryRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected CertificateRepositoryInterface $certificateRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected ProductRepositoryInterface $productRepository,
        protected UserRepositoryInterface $userRepository
    ) {}


    public function certificates(Request $request, $type = 'all')
    {
        $certificates = $this->certificateRepository
        ->query()
        ->whereNull('deleted_at')
        ->select('id', 'name', 'type', 'description')
        ->when($type != 'all', function ($query) use ($type) {
            $query->where('type', $type);
        })
        ->get();

        return response()->json($certificates);
    }

    public function categories(Request $request, $type = 'all')
    {
        $categories = $this->categoryRepository
        ->query()
        ->whereNull('deleted_at')
        ->select('id', 'name', 'type', 'description')
        ->when($type != 'all', function ($query) use ($type) {
            $query->where('type', $type);
        })
        ->get();

        return response()->json($categories);
    }

    public function countries(Request $request)
    {
        $countries = $this->countryRepository
        ->query()
        ->select('id', 'name')
        ->get();

        return response()->json($countries);
    }

    public function users(Request $request)
    {
        $users = $this->userRepository
        ->query()
        ->whereNull('deleted_at')
        ->whereDoesntHave('roles', function($query) {
            $query->where('name', 'admin');
        })
        ->select('id', 'name')
        ->get();

        return response()->json($users);
    }



}
