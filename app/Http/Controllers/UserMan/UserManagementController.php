<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function dashboard()
    {
        return view('UserManagement.dashboard.dashboard');
    }

    public function calendar()
    {
        return view('UserManagement.calendar.calendar');
    }

    public function profile()
    {
        return view('UserManagement.profile.profile');
    }

    public function users()
    {
         // Fetch all users from the database
        $users = User::all();

        $totalUsers = User::count();
        $userManagementCount = User::where('role', 'UserManagement')->count();
        $editorCount = User::where('role', 'Editor')->count();
        $viewerCount = User::where('role', 'Viewer')->count();

        return view('UserManagement.users.users', compact(
            'totalUsers',
            'userManagementCount',
            'editorCount',
            'viewerCount',
            'users'
        ));
    }

    public function activity_log()
    {
        return view('UserManagement.activity_log.activity_log');
    }

    public function history()
    {
        return view('UserManagement.history.history');
    }

    public function archive()
    {
        return view('UserManagement.archive.archive');
    }

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