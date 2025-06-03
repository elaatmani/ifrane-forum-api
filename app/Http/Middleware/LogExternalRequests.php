<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ExternalRequest;
use Symfony\Component\HttpFoundation\Response;

class LogExternalRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('get') || $request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
            ExternalRequest::create([
                'actor_id' => auth()->id(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => json_encode($request->headers->all()),
                'body' => $request->getContent(),
                'response_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);
        }

        return $response;
    }
}
