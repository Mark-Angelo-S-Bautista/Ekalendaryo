<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EditorRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if the user is logged in
        if (!Auth::check()) {
            return redirect('/'); // Redirect unauthenticated users
        }

        $user = Auth::user();
        
        // 2. Check if the user's role is 'Editor'
        if ($user->role !== 'Editor') {
            // If they don't have the role, redirect them to a safe page or show a 403
            // Redirecting to the dashboard is often a good user experience.
            return redirect('/')->with('error', 'You do not have access to that page.');
        }

        // 3. If the user is an Editor, allow the request to proceed
        return $next($request);
    }
}