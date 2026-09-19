<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;"
        );
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}