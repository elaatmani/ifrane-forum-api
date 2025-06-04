<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        if (auth('web')->check()) {
            $user = auth('web')->user();
            // Check the user's status
            if ($user->is_active) {
                $user->update([
                    'last_action_at' => now(),
                ]);
                // If status is true, allow the request to proceed
                return $next($request);
            } else {
                auth('web')->logout();
                // If status is false, return a 401 Unauthorized response
                return response()->json(['message' => 'Your account is not active', 'code' => 'NOT_ACTIVE'], 401);
            }
        }
        
        auth('web')->logout();
        // If the user is not authenticated, return a 401 Unauthorized response
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
