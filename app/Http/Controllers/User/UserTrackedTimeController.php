<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserTrackedTimeController extends Controller
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id, $date)
    {


        $selectedDate = $date; // Example date

        $sessions = DB::table('user_sessions')
            ->select('login_time', 'logout_time')
            // ->select(DB::raw('HOUR(login_time) as hour, SUM(TIMESTAMPDIFF(MINUTE, login_time, IFNULL(logout_time, NOW()))) as time_spent'))
            ->where('user_id', $id)
            ->whereDate('login_time', $selectedDate)
            ->whereNotNull('logout_time')
            // ->groupBy(DB::raw('HOUR(login_time)'))
            ->get();

            

        return response()->json([
            'code' => 'SUCCESS',
            'data' =>  $sessions
        ], 200);
    }

    public function byDays(Request $request, $id)
    {
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::now()->subDays($i)->toDateString());
        }

        // Step 2: Query the user_sessions table
        $results = DB::table('user_sessions')
            ->select(DB::raw('DATE(login_time) as date'), DB::raw('SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as minutes'))
            ->whereNotNull('logout_time')
            ->where('user_id', $id)
            ->whereBetween(DB::raw('DATE(login_time)'), [Carbon::now()->subDays(6)->toDateString(), Carbon::now()->toDateString()])
            ->groupBy(DB::raw('DATE(login_time)'), 'user_id')
            ->get()
            ->keyBy('date'); // Key the results by date for easy mapping

        // Step 3: Merge the results with the generated dates
        $finalResults = $dates->map(function ($date) use ($results) {
            return [
                'date' => $date,
                'minutes' => $results->get($date)->minutes ?? 0 // Return 0 if no data for that date
            ];
        });

        return response()->json([
            'code' => 'SUCCESS',
            'data' =>  $finalResults
        ], 200);
    }
}
