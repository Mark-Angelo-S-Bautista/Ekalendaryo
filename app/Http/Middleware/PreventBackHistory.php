<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Http\Response; // <-- Crucial: Use Laravel's Response class

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse // SymfonyResponse is usually required here for contract compliance
    {
        $response = $next($request);

        // Check if the response is one of Laravel's HTTP responses (which includes the header() method)
        if ($response instanceof Response) {
             return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                             ->header('Pragma', 'no-cache')
                             ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Return other response types (e.g., JSON, redirects, etc.) without modification
        return $response;
    }
}