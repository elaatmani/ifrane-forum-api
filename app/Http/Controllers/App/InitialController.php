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
use App\Repositories\Contracts\ProductRepositoryInterface;

class InitialController extends Controller
{
    protected $orderRepository;
    protected $userRepository;
    protected $productRepository;

    protected $products;
    protected $deliveries;
    protected $cities;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
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
        return [
            'code' => 'SUCCESS',
        ];
    }

}
