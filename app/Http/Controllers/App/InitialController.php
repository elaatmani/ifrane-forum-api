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
        OrderRepositoryInterface $orderRepository,
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;

        $this->products = $productRepository->query()->orderBy('id', 'desc')->get()->transform(fn ($product) => new ProductForOrderCollection($product));
        $this->deliveries = $userRepository->deliveries()->transform(fn ($user) => new UserDeliveryResource($user));
        $this->cities = City::all()->transform(fn ($city) => new CityResource($city));
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $response = [
            'role' => auth()->user()->roles->first()->name,
            'products' => $this->products,
            'deliveries' => $this->deliveries,
            'cities' => $this->cities,
        ];
        switch (auth()->user()->roles->first()->name) {
            case 'agent':
                $result = $this->agent();
                break;
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
        $confirmed = $this->orderRepository->query()->confirmed()->count();
        $total = $this->orderRepository->query()->count();
        $totalNotDuplicated = $this->orderRepository->query()->where('agent_status', '!=', 'duplicate')->count();

        $rates = [
            'confirmed' => $totalNotDuplicated > 0 ? round(($confirmed * 100) / $totalNotDuplicated, 2) : 0,
        ];

        return [
            'count' => [
                'orders' => $total,
                'confirmed' => $confirmed,
                'canceled' => $this->orderRepository->query()->canceled()->count(),
            ],
            'rates' => $rates,

            'code' => 'SUCCESS',
        ];
    }

    public function agent()
    {
        $rates = [];

        return [
            'count' => [
                'orders' => $this->orderRepository->query()->where('agent_id', auth()->id())->count(),
                'new' => $this->orderRepository->query()->WhereNotAssigned()->count(),
            ],

            'rates' => $rates,

            'code' => 'SUCCESS',
        ];
    }
}
