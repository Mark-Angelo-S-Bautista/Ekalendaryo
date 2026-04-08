<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController
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

            if ($user && $user->must_change_password) {
                return redirect()->route('firstLogin.password.form');
            }
            
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

    public function showFirstLoginPasswordForm()
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            if ($user && $user->role === 'Editor') {
                return redirect()->route('Editor.dashboard');
            }

            if ($user && $user->role === 'UserManagement') {
                return redirect()->route('UserManagement.dashboard');
            }

            if ($user && $user->role === 'Viewer') {
                return redirect()->route('Viewer.dashboard');
            }

            return redirect('/');
        }

        return view('Auth.first_login_change_password');
    }

    public function updateFirstLoginPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('Auth.login');
        }

        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from your current password.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
        ]);

        if ($user->role === 'Editor') {
            return redirect()->route('Editor.dashboard');
        }

        if ($user->role === 'UserManagement') {
            return redirect()->route('UserManagement.dashboard');
        }

        if ($user->role === 'Viewer') {
            return redirect()->route('Viewer.dashboard');
        }

        return redirect('/');
    }
}
