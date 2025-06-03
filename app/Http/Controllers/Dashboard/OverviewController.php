<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enums\OrderDeliveryEnum;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Enums\OrderConfirmationEnum;
use App\Http\Controllers\Controller;
use App\Services\Analytics\OrderAnalyticsService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OverviewController extends Controller
{
    protected $userRepository;
    protected $orderRepository;
    protected $orderAnalyticsService;

    public function __construct(UserRepositoryInterface $userRepository, OrderRepositoryInterface $orderRepository)
    {
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->orderAnalyticsService = new OrderAnalyticsService();
    }

    public function admin(Request $request)
    {
        $params = $request->all();

        $agent_status = data_get($params, 'agent_status', []);

        $created_at_from = data_get($params, 'created_at.from');
        $created_at_to = data_get($params, 'created_at.to');


        if ($created_at_from) {
            $created_at_from = Carbon::parse($created_at_from);
        }

        if ($created_at_to) {
            $created_at_to = Carbon::parse($created_at_to);
        }
        $revenue = $this->orderAnalyticsService->getRevenue($params);
        $orders = $this->orderRepository->query()
            ->when(count($agent_status) > 0, function ($query) use ($agent_status) {
                $query->whereIn('agent_status', $agent_status)->get();
            })
            ->when($created_at_from, function ($query) use ($created_at_from) {
                $query->whereDate('created_at', '>=', $created_at_from)->get();
            })
            ->when($created_at_to, function ($query) use ($created_at_to) {
                $query->whereDate('created_at', '<=', $created_at_to)->get();
            })
            ->count();
        $confirmed = $this->orderRepository->query()
            ->when($created_at_from, function ($query) use ($created_at_from) {
                $query->whereDate('created_at', '>=', $created_at_from)->get();
            })
            ->when($created_at_to, function ($query) use ($created_at_to) {
                $query->whereDate('created_at', '<=', $created_at_to)->get();
            })
            ->when(count($agent_status) > 0, function ($query) use ($agent_status) {
                $query->whereIn('agent_status', $agent_status)->get();
            })
            ->where('agent_status', OrderConfirmationEnum::CONFIRMED->value)->count();
        $delivered = $this->orderRepository->query()
            ->when($created_at_from, function ($query) use ($created_at_from) {
                $query->whereDate('created_at', '>=', $created_at_from)->get();
            })
            ->when(count($agent_status) > 0, function ($query) use ($agent_status) {
                $query->whereIn('agent_status', $agent_status)->get();
            })
            ->when($created_at_to, function ($query) use ($created_at_to) {
                $query->whereDate('created_at', '<=', $created_at_to)->get();
            })
            ->where('agent_status', OrderConfirmationEnum::CONFIRMED->value)->where('delivery_status', OrderDeliveryEnum::DELIVERED->value)->count();

        return response()->json([
            'data' => [
                'revenue' => $revenue,
                'orders' => $orders,
                'confirmed' => $confirmed,
                'delivered' => $delivered,
            ]
        ]);
    }
}
