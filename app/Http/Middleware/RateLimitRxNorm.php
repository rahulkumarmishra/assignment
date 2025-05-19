<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitRxNorm
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'rxnorm_requests_' . $request->ip();

        $executed = RateLimiter::attempt(
            $key,
            10, // 10 requests
            function() {},
            30 // per minute
        );

        if (!$executed) {
            return response()->json(['message' => 'Too many requests'], 429);
        }

        return $next($request);
    }
}