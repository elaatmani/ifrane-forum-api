<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderConfirmationEnum;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderListResource;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\NawrisService;

class OrderListController extends Controller
{
    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $per_page = $request->per_page ?? 10;

        $criteria = SearchHelper::order($request);

        $orders = $this->repository->search($criteria, false)
            ->where(function ($q) {
                $q->when(!auth()->user()->hasRole('admin'), function ($query) {
                    if (auth()->user()->hasRole('followup')) {
                        return $query
                            ->where([
                                'followup_id' => auth()->id()
                            ]);
                    } elseif (auth()->user()->hasRole('agent')) {
                        return $query->where('agent_id', auth()->id());
                    }
                });
            });

        // Check if sort parameters were provided
        $sort_field = $request->sort_field ?? null;
        $sort_direction = $request->sort_direction ?? 'desc';

        // Validate sort direction to prevent SQL injection
        if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
            $sort_direction = 'desc';
        }

        // For followup users, always use followup_assigned_at sorting regardless of parameters
        if (auth()->user()->hasRole('followup')) {
            $orders->orderBy('followup_assigned_at', 'desc');
        } else {
            // Validate sort field to prevent SQL injection
            $allowed_sort_fields = ['id', 'created_at', 'updated_at', 'followup_assigned_at', 'reconfirmed_at'];
            if ($sort_field && in_array($sort_field, $allowed_sort_fields)) {
                $orders->orderBy($sort_field, $sort_direction);
            } else {
                // Default sorting for non-followup users
                $orders->orderBy('id', 'desc');
            }
        }

        $orders = $orders->paginate($per_page);
        $orders->getCollection()->transform(fn($order) => new OrderListResource($order));

        return response()->json([
            'data' => $orders,
            'code' => 'SUCCESS',
        ], 200);
    }
}
