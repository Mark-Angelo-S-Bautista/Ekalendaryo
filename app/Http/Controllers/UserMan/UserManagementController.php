<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function dashboard()
    {
        //fetch the users info
        $user = Auth::user(); // Get the currently logged-in user
        $office_name = $user->office_name;

            // Fetch all events
        $events = Event::all();

        // Fetch all departments from database (so it’s dynamic)
        $departments = Department::pluck('department_name')->toArray(); // adjust column name if needed

        // Total events
        $totalEvents = $events->count();

        // Count per department
        $departmentCounts = $events
            ->groupBy('department')
            ->map(fn($group) => $group->count());

        // Merge event counts with department list, fill missing ones with 0
        $finalDeptCounts = collect($departments)->mapWithKeys(function ($dept) use ($departmentCounts) {
            return [$dept => $departmentCounts[$dept] ?? 0];
        });

        // Upcoming events (within next 30 days)
        $upcomingEvents = $events
            ->whereBetween('date', [now(), now()->addDays(30)])
            ->sortBy('date')
            ->take(2);

        return view('UserManagement.dashboard.dashboard', [
            'user' => $user,
            'totalEvents' => $totalEvents,
            'departmentCounts' => $finalDeptCounts,
            'upcomingEvents' => $upcomingEvents,
            'office_name' => $office_name,
        ]);
    }

    public function calendar()
    {
        $events = Event::all()->map(function ($event) {
            return [
                'date' => $event->date,
                'title' => $event->title,
                'description' => $event->description ?? 'No description provided.', // default if null
                'timeStart' => $event->start_time,
                'timeEnd' => $event->end_time,
                'location' => $event->location,
                'sy' => $event->school_year,
                'type' => strtolower(str_replace(['/', ' '], '_', $event->department ?? 'general')),
                'organizer' => $event->department ?? 'N/A',
            ];
        });

        return view('UserManagement.calendar.calendar', ['events' => $events]);
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
        // Validate only the department_name
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:departments,department_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all(),
            ]);
        }

        $department = Department::create([
            'department_name' => $request->department_name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Department added successfully!',
            'department' => $department,
        ]);
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
        return redirect()->route('Auth.login');
    }
}