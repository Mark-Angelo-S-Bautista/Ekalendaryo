<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
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

    public function users(Request $request)
    {
        $query = $request->input('query'); // get the text from the search bar

        // Fetch users based on search query if exists
        $users = User::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })->get();

        // Count users by role
        $totalUsers = User::count();
        $userManagementCount = User::where('role', 'UserManagement')->count();
        $editorCount = User::where('role', 'Editor')->count();
        $viewerCount = User::where('role', 'Viewer')->count();

        $departments = Department::all(); // ✅ fetch all departments

        return view('UserManagement.users.users', compact(
            'totalUsers',
            'userManagementCount',
            'editorCount',
            'viewerCount',
            'users',
            'query',
            'departments'
        ));
    }

    public function addDepartment(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
        ]);

        Department::create([
            'department_name' => $request->department_name,
        ]);

        return redirect()
            ->route('UserManagement.users')
            ->with('success', 'Department added successfully!');
    }

    public function deleteDepartment($id)
    {
        Department::findOrFail($id)->delete();
        return back()->with('success', 'Department deleted successfully!');
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