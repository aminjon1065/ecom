<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://telegram.org; ".
            "style-src 'self' 'unsafe-inline'; ".
            "img-src 'self' data: blob: https:; ".
            "font-src 'self' data:; ".
            "connect-src 'self'; ".
            "frame-src 'self' https://oauth.telegram.org; ".
            "frame-ancestors 'self';"
        );

        return $response;
    }
}
