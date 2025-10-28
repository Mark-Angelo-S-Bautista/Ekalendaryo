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

    public function index()
    {
        return view('Auth.login');
    }

    public function store(Request $request)
    {
        // 1. Validate the input fields
        $credentials = $request->validate([
            'userId' => ['required'], 
            'password' => ['required', 'string'],
        ]);

        // 2. Attempt to authenticate the user
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
        
            $request->session()->regenerate();
            
            // Get the authenticated user
            $user = Auth::user();
            
            if ($user && $user->role === 'Editor') {
                return redirect()->route('Editor.dashboard');
            } 
            
            if ($user && $user->role === 'UserManagement') {
                return redirect()->route('UserManagement.dashboard');
            }

            if ($user && $user->role === 'Viewer') {
                return redirect()->route('Viewer.dashboard');
            }
            
            // Fallback for any user that logged in but didn't match a specific role
            return redirect('/');
        
        }

        // 3. Handle failure: throw an error message back to the login form
        throw ValidationException::withMessages([
            'userId' => ('Incorrect ID or Password'), // The default message: 'These credentials do not match our records.'
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
