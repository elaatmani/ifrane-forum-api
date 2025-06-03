<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function agents(Request $request)
    {
        $users = Role::findByName('agent', 'web')->users()
            ->with(['agent_orders' => function ($query) {
                $query->select('agent_id', 'agent_status', DB::raw('count(*) as status_count'))
                    ->groupBy('agent_id', 'agent_status');
            }])
            ->withCount('agent_orders')
            ->paginate(10);

        $users->each(function ($user) {
            $totalOrders = $user->agent_orders_count;
            $statusCounts = $user->agent_orders->groupBy('agent_status');

            $percentages = $statusCounts->mapWithKeys(function ($orders, $status) use ($totalOrders) {
                return [$status => ($orders->first()->status_count / $totalOrders) * 100];
            });

            $user->agent_status_percentages = $percentages;
        });

        $users->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'agent_status_percentages' => $user->agent_status_percentages,
                'agent_orders' => $user->agent_orders,
                'last_action_at' => $user->last_action_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'data' => $users
        ]);
    }


    public function followups(Request $request)
    {
        $users = Role::findByName('followup', 'web')->users()
            ->with(['followup_orders' => function ($query) {
                $query->select('followup_id', 'followup_status', DB::raw('count(*) as status_count'))
                    ->groupBy('followup_id', 'followup_status');
            }])
            ->withCount('followup_orders')
            ->paginate(10);

        $users->each(function ($user) {
            $totalOrders = $user->followup_orders_count;
            $statusCounts = $user->followup_orders->groupBy('followup_status');

            $percentages = $statusCounts->mapWithKeys(function ($orders, $status) use ($totalOrders) {
                return [$status => ($orders->first()->status_count / $totalOrders) * 100];
            });

            $user->followup_status_percentages = $percentages;
        });

        $users->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'followup_status_percentages' => $user->followup_status_percentages,
                'followup_orders' => $user->followup_orders,
                'last_action_at' => $user->last_action_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'data' => $users
        ]);
    }
}
