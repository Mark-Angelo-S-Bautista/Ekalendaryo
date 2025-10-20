<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'guest' => ['except' => 'destroy'],
        ];
    }

    /**
     * Display the login view. (Maps to GET /login)
     */
    public function index()
    {
        return view('Auth.login');
    }

    /**
     * Handle the incoming authentication request. (Maps to POST /login)
     */
    public function store(Request $request)
    {
        // 1. Validate the input fields
        $credentials = $request->validate([
            // You can change 'email' to 'username' if you use usernames
            'userId' => ['required'], 
            'password' => ['required', 'string'],
        ]);

        // 2. Attempt to authenticate the user
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
        
        $request->session()->regenerate();
        
        // 🛑 NEW LOGIC STARTS HERE 🛑
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Check user role and redirect to the correct named route
        // NOTE: This assumes your User model has a 'role' column 
        //       or a 'hasRole' method. Adjust the condition as needed.
        if ($user && $user->role === 'Editor') {
            return redirect()->route('Editor.dashboard');
        } 
        
        // If they have the other role (UserManagement), or any other role
        // For UserManagement roles, redirect to its dashboard
        if ($user && $user->role === 'UserManagement') {
             return redirect()->route('UserManagement.dashboard');
        }
        
        // Fallback for any user that logged in but didn't match a specific role
        // This is a safe route, but you should adjust the logic above to match all roles.
        return redirect('/');
        
    }

        // 3. Handle failure: throw an error message back to the login form
        throw ValidationException::withMessages([
            'userId' => __('auth.failed'), // The default message: 'These credentials do not match our records.'
        ]);
    }

    /**
     * Log the user out of the application. (Maps to POST /logout)
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        // Invalidate the current session
        $request->session()->invalidate();

        // Regenerate the CSRF token for the next request
        $request->session()->regenerateToken();

        // Redirect the user back to the homepage or login page
        return redirect('/');
    }
}
