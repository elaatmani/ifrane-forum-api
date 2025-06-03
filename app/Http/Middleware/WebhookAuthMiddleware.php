<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebhookAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Webhook-Token');
        
        if (!$token || $token !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
} 