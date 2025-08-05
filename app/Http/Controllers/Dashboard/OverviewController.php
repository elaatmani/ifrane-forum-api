<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $companies = DB::table('companies')
        ->whereNull('deleted_at')
        ->count();

        $users = DB::table('users')
        ->whereNull('deleted_at')
        ->count();

        $sessions = DB::table('sessions')
        ->whereNull('deleted_at')
        ->count();

        $products = DB::table('products')
        ->whereNull('deleted_at')
        ->count();

        $services = DB::table('services')
        ->whereNull('deleted_at')
        ->count();

        $activeSessions = DB::table('sessions')
        ->whereNull('deleted_at')
        ->where('is_active', true)
        ->count();

        return response()->json([
            'companies' => $companies,
            'users' => $users,
            'sessions' => $sessions,
            'products' => $products,
            'services' => $services,
            'active_sessions' => $activeSessions,
        ]);



    }
}
