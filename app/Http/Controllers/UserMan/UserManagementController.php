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
        $user = Auth::user();
        $office_name = $user->office_name;

        // Fetch all events once
        $events = Event::all();

        // Fetch departments
        $departments = Department::pluck('department_name')->toArray();

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | FILTER UPCOMING EVENTS (future + within next 30 days)
        |--------------------------------------------------------------------------
        */
        $upcomingEvents = $events
            ->filter(function ($event) use ($now) {

                // Combine date + start_time → proper datetime comparison
                $eventDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);

                // Must NOT be past
                if ($eventDateTime->lessThan($now)) {
                    return false;
                }

                // Must be within next 30 days
                return $eventDateTime->between($now, $now->copy()->addDays(30));
            })
            ->sortBy(function ($event) {
                return \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            });

        /*
        |--------------------------------------------------------------------------
        | COUNT ONLY UPCOMING EVENTS
        |--------------------------------------------------------------------------
        */

        // TOTAL upcoming events
        $totalEvents = $upcomingEvents->count();

        // COUNT per department (only upcoming)
        $departmentCounts = $upcomingEvents
            ->groupBy('department')
            ->map(fn($group) => $group->count());

        // Ensure every department appears even if count=0
        $finalDeptCounts = collect($departments)->mapWithKeys(function ($dept) use ($departmentCounts) {
            return [$dept => $departmentCounts[$dept] ?? 0];
        });

        // Show only FIRST 2 events in Upcoming Preview Cards
        $upcomingEventsPreview = $upcomingEvents->take(2);

        return view('UserManagement.dashboard.dashboard', [
            'user' => $user,
            'totalEvents' => $totalEvents,
            'departmentCounts' => $finalDeptCounts,
            'upcomingEvents' => $upcomingEventsPreview,
            'office_name' => $office_name,
        ]);
    }

    public function adduser(Request $request)
    {
        // Base rules
        $rules = [
            'name' => 'required|string|max:255|unique:users,name',
            'userId' => 'required|string|max:255|unique:users,userId',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'department' => 'required|string',
            'title' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ];

        // Conditional rules
        if ($request->title === 'Student') {
            $rules['yearlevel'] = 'required|string|max:50';
            $rules['section'] = 'required|string|max:50';
        } elseif ($request->title === 'Offices') {
            $rules['office_name'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        $errors = [];

        // Custom check for duplicate user
        if (User::where('name', $request->name)->where('userId', $request->userId)->exists()) {
            $errors['userId'][] = 'User with this Name and ID already exists.';
        }

        // Add validator errors
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $errors[$field] = $messages;
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'errors' => $errors,
            ]);
        }

        // Create user
        User::create([
            'name' => $request->name,
            'office_name' => $request->office_name,
            'userId' => $request->userId,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'title' => $request->title,
            'yearlevel' => $request->yearlevel,
            'section' => $request->section,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User added successfully!',
        ]);
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
        })->paginate(4);

        // ----------------------------------------------------------------------
        // ✅ FIX: Count users by TITLE instead of ROLE to match dashboard cards
        // ----------------------------------------------------------------------
        $totalUsers = User::count();
        
        // Count users by specific titles/departments
        $studentCount = User::where('title', 'Student')->count();
        $facultyCount = User::where('title', 'Faculty')->count();
        $deptHeadCount = User::where('title', 'Department Head')->count();
        
        // Count 'Offices' users (assuming 'Offices' is identified by department name or title)
        // We use where('department', 'OFFICES') based on your previous Blade logic
        $officesCount = User::where('department', 'OFFICES')->count(); 

        // Fetch all departments
        $departments = Department::all(); 

        return view('UserManagement.users.users', compact(
            'totalUsers',
            'studentCount',      // New variable for Student card
            'facultyCount',      // New variable for Faculty card
            'deptHeadCount',     // New variable for Department Head card
            'officesCount',      // New variable for Offices card
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

    // public function deleteDepartment($id)
    // {
    //     Department::findOrFail($id)->delete();
    //     return back()->with('success', 'Department deleted successfully!');
    // }

    public function activity_log()
    {
        return view('UserManagement.activity_log.activity_log');
    }

    public function calendar()
    {
        $events = Event::all()->map(function ($event) {
            
            // --- NEW SANITIZATION LOGIC START ---
            $targetYearLevels = $event->target_year_levels;

            // If casting failed (likely because data is a JSON string *not* an array),
            // we manually decode it. If that fails too, default to an empty array.
            if (is_string($targetYearLevels)) {
                $targetYearLevels = json_decode($targetYearLevels, true) ?? [];
            } elseif (!is_array($targetYearLevels)) {
                // Handle null, undefined, or any other unexpected type
                $targetYearLevels = [];
            }
            // --- NEW SANITIZATION LOGIC END ---

            return [
                'date' => $event->date,
                'title' => $event->title,
                'description' => $event->description ?? 'No description provided.',
                'moreDetails' => $event->more_details ?? 'No additional details.', // <-- Add this
                'timeStart' => $event->start_time,
                'timeEnd' => $event->end_time,
                'location' => $event->location,
                'sy' => $event->school_year,
                'type' => strtolower(str_replace(['/', ' '], '_', $event->department ?? 'general')),
                'organizer' => $event->department ?? 'N/A',
                'targetYearLevels' => $targetYearLevels, // Use the sanitized variable
            ];
        });

        return view('UserManagement.calendar.calendar', ['events' => $events]);
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