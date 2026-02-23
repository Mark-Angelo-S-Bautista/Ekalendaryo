<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\SchoolYear;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\VerifyNewEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserManagementController
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $office_name = $user->office_name;

        // Current school year
        $currentSchoolYear = SchoolYear::where('is_active', 1)->first();
        $currentSchoolYearName = $currentSchoolYear
            ? $currentSchoolYear->school_year
            : 'N/A';

        // Fetch all events
        $events = Event::with('user')->get();

        // Departments
        $departments = Department::pluck('department_name')->toArray();

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | FILTER UPCOMING + ONGOING EVENTS (ACTIVE EVENTS)
        |--------------------------------------------------------------------------
        */
        $activeEvents = $events->filter(function ($event) use ($now) {

            // ❌ Exclude cancelled & completed
            if (in_array($event->computed_status, ['cancelled', 'completed'])) {
                return false;
            }

            $start = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            $end   = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);

            // ✅ ONGOING: started already but not ended
            if ($start->lessThanOrEqualTo($now) && $end->greaterThanOrEqualTo($now)) {
                return true;
            }

            // ✅ UPCOMING: within next 30 days
            if ($start->greaterThan($now) && $start->lessThanOrEqualTo($now->copy()->addDays(356))) {
                return true;
            }

            return false;
        })
        ->sortBy(function ($event) {
            return \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
        })
        ->values(); // Reset keys for pagination

        /*
        |--------------------------------------------------------------------------
        | COUNTS
        |--------------------------------------------------------------------------
        */

        // Total active events
        $totalEvents = $activeEvents->count();

        // Count per department
        $departmentCounts = $activeEvents
            ->groupBy('department')
            ->map(fn ($group) => $group->count());

        // Ensure all departments appear
        $finalDeptCounts = collect($departments)->mapWithKeys(function ($dept) use ($departmentCounts) {
            return [$dept => $departmentCounts[$dept] ?? 0];
        });

        // Paginate with 8 per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 8;
        $currentItems = $activeEvents->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedEvents = new LengthAwarePaginator(
            $currentItems,
            $activeEvents->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('UserManagement.dashboard.dashboard', [
            'user' => $user,
            'totalEvents' => $totalEvents,
            'departmentCounts' => $finalDeptCounts,
            'upcomingEvents' => $paginatedEvents,
            'office_name' => $office_name,
            'currentSchoolYearName' => $currentSchoolYearName,
        ]);
    }

    public function dashboardSearch(Request $request)
    {
        $query = $request->input('query', '');
        $now = now();

        $events = Event::with('user')
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->get()
            ->filter(function ($event) use ($now) {
                // ❌ Exclude cancelled & completed
                if (in_array($event->computed_status, ['cancelled', 'completed'])) {
                    return false;
                }

                $start = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                $end   = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);

                // ✅ ONGOING: started already but not ended
                if ($start->lessThanOrEqualTo($now) && $end->greaterThanOrEqualTo($now)) {
                    return true;
                }

                // ✅ UPCOMING: within next 30 days
                if ($start->greaterThan($now) && $start->lessThanOrEqualTo($now->copy()->addDays(30))) {
                    return true;
                }

                return false;
            })
            ->sortBy(function ($event) {
                return \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            })
            ->values();

        return response()->json(['events' => $events]);
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

        // Get active school year
        $activeSchoolYear = SchoolYear::where('is_active', 1)->first();
        $schoolYearId = $activeSchoolYear ? $activeSchoolYear->id : null;

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
            'school_year_id' => $schoolYearId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User added successfully!',
        ]);
    }


    public function profile()
    {
        $user = Auth::user();
        return view('UserManagement.profile.profile', compact('user'));
    }

    // Update name or userId
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'userId' => 'required|string|max:50|unique:users,userId,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'userId' => $request->userId,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    // Update email with verification
    public function updateEmail(Request $request)
    {
        // Validate email first
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'current_password' => 'required',
        ]);

        // Check password
        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ]
            ], 422);
        }

        // Generate token
        $token = Str::random(64);

        // Save pending email
        auth()->user()->update([
            'pending_email' => $request->new_email,
            'email_change_token' => $token,
        ]);

        // Send verification email
        Mail::to($request->new_email)->send(
            new VerifyNewEmail($token)
        );

        return response()->json([
            'message' => 'Verification email sent to your new email address.',
        ]);
    }

    public function verifyNewEmail($token)
    {
        $user = User::where('email_change_token', $token)->firstOrFail();

        $user->update([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_change_token' => null,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('UserManagement.profile')
            ->with('success', 'Your email has been updated successfully.');
    }

    // Update password
    public function updatePassword(Request $request)
    {
        // Validate inputs FIRST (except current password check)
        $validator = \Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check current password
        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        auth()->user()->update([
            'password' => bcrypt($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    public function users(Request $request)
    {
        $query = $request->input('query'); // get the text from the search bar

        // Fetch users based on search query if exists
        $users = User::where('status', 'active') // Only active users
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name', 'asc') // ✅ Sort alphabetically by name
            ->get();

        // ----------------------------------------------------------------------
        // ✅ FIX: Count users by TITLE instead of ROLE to match dashboard cards
        // ----------------------------------------------------------------------
        $totalUsers = User::where('status', 'active')->count(); // Only active users
    
        // Count users by specific titles/departments (only active users)
        $studentCount = User::where('status', 'active')->where('title', 'Student')->count();
        $facultyCount = User::where('status', 'active')->where('title', 'Faculty')->count();
        $deptHeadCount = User::where('status', 'active')->where('title', 'Department Head')->count();
        
        // Count 'Offices' users (only active users)
        $officesCount = User::where('status', 'active')->where('department', 'OFFICES')->count(); 

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
        // Validate the department_name and max_year_levels
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'max_year_levels' => 'nullable|string|in:1stYear,2ndYear,3rdYear,4thYear',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all(),
            ]);
        }

        $department = Department::create([
            'department_name' => $request->department_name,
            'max_year_levels' => $request->max_year_levels ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Department added successfully!',
            'department' => $department,
        ]);
    }

    public function activity_log()
    {
        return view('UserManagement.activity_log.activity_log');
    }

    public function calendar()
    {
        $user = Auth::user();
        $departments = Department::orderBy('department_name')->get();
        
        // Get unique office names from all OFFICES users
        $officeNames = User::where('department', 'OFFICES')
            ->where('status', 'active')
            ->whereNotNull('office_name')
            ->distinct('office_name')
            ->pluck('office_name')
            ->sort()
            ->values();

        $events = Event::with('user')->whereNotIn('status', ['cancelled', 'archived'])
            ->get()
            ->map(function ($event) {

                $targetYearLevels = $event->target_year_levels;

                if (is_string($targetYearLevels)) {
                    $targetYearLevels = json_decode($targetYearLevels, true) ?? [];
                } elseif (!is_array($targetYearLevels)) {
                    $targetYearLevels = [];
                }

                // If event is from OFFICES department, use creator's office name
                $displayDept = $event->department;
                if ($event->department === 'OFFICES' && $event->user && $event->user->office_name) {
                    $displayDept = $event->user->office_name;
                }

                return [
                    'date' => $event->date,
                    'title' => $event->title,
                    'description' => $event->description ?? 'No description provided.',
                    'moreDetails' => $event->more_details ?? 'No additional details.',
                    'timeStart' => $event->start_time,
                    'timeEnd' => $event->end_time,
                    'status' => $event->computed_status,
                    'location' => $event->location,
                    'sy' => $event->school_year,
                    'type' => strtolower(str_replace(['/', ' '], '_', $displayDept)),
                    'organizer' => $displayDept,
                    'targetYearLevels' => $targetYearLevels,
                ];
            });

        return view('UserManagement.calendar.calendar', compact('events', 'departments', 'user', 'officeNames'));
    }

    public function archive(Request $request)
    {
        $title = $request->get('title');
        $schoolYear = $request->get('school_year');
        $department = $request->get('department');

        $archivedUsers = User::whereIn('status', ['dropped', 'fired', 'graduated'])
            ->when($title, function ($q) use ($title) {
                $q->where('title', $title);
            })
            ->when($schoolYear, function ($q) use ($schoolYear) {
                $q->where('school_year_id', $schoolYear);
            })
            ->when($department, function ($q) use ($department) {
                $q->where('department', $department);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get unique titles for filter dropdown
        $titles = User::whereNotNull('title')
            ->distinct()
            ->pluck('title');

        // Get unique school years for archived users
        $schoolYears = SchoolYear::whereIn('id', 
            User::whereIn('status', ['dropped', 'fired', 'graduated'])
                ->whereNotNull('school_year_id')
                ->distinct()
                ->pluck('school_year_id')
        )->get();

        // Get unique departments for archived users
        $departments = User::whereIn('status', ['dropped', 'fired', 'graduated'])
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('UserManagement.archive.archive', compact('archivedUsers', 'titles', 'title', 'schoolYears', 'schoolYear', 'departments', 'department'));
    }

    public function changeSchoolYear()
    {
        DB::transaction(function () {

            $currentSY = SchoolYear::where('is_active', 1)->lockForUpdate()->firstOrFail();

            // 2025-2026 → 2026-2027
            [$start, $end] = explode('-', $currentSY->school_year);
            $nextSY = ($start + 1) . '-' . ($end + 1);

            // --------------------------------------------------
            // 1. ARCHIVE COMPLETED EVENTS
            // --------------------------------------------------
            Event::where('status', 'completed')
                ->update(['status' => 'archived']);

            // --------------------------------------------------
            // 2. CLEAR RELATED TABLES (NEW SCHOOL YEAR RESET)
            // --------------------------------------------------
            DB::table('activity_logs')->delete();
            DB::table('event_attendees')->delete();
            DB::table('feedback')->delete();

            // --------------------------------------------------
            // 3. DEACTIVATE CURRENT SCHOOL YEAR
            // --------------------------------------------------
            $currentSY->update(['is_active' => 0]);

            // --------------------------------------------------
            // 4. CREATE NEW SCHOOL YEAR
            // --------------------------------------------------
            $newSY = SchoolYear::create([
                'school_year' => $nextSY,
                'is_active' => 1
            ]);

            // --------------------------------------------------
            // 5 & 6. GRADUATE/PROMOTE STUDENTS BASED ON DEPARTMENT MAX YEAR LEVEL
            // --------------------------------------------------
            // Get all active students
            $students = User::where('title', 'Student')
                ->where('status', 'active')
                ->get();

            // Map year levels to numeric values
            $yearLevelMap = [
                '1stYear' => 1,
                '2ndYear' => 2,
                '3rdYear' => 3,
                '4thYear' => 4,
                '5thYear' => 5,
            ];

            // Reverse map for promotion
            $promotionMap = [
                '1stYear' => '2ndYear',
                '2ndYear' => '3rdYear',
                '3rdYear' => '4thYear',
                '4thYear' => '5thYear',
            ];

            foreach ($students as $student) {
                // Get student's department's max year level
                $department = Department::where('department_name', $student->department)->first();
                
                if (!$department) {
                    continue; // Skip if department not found
                }

                $studentYearLevel = $yearLevelMap[$student->yearlevel] ?? 0;
                
                if ($studentYearLevel >= $department->max_year_levels) {
                    // Student is already at or beyond max year level, graduate them
                    $student->update([
                        'status' => 'graduated',
                        'school_year_id' => $currentSY->id
                    ]);
                } else {
                    // Student is below max, check if promotion would exceed max
                    $nextYear = $promotionMap[$student->yearlevel] ?? $student->yearlevel;
                    $nextYearLevel = $yearLevelMap[$nextYear] ?? 0;
                    
                    if ($nextYearLevel > $department->max_year_levels) {
                        // Promoting would exceed max year level, so graduate instead
                        $student->update([
                            'status' => 'graduated',
                            'school_year_id' => $currentSY->id
                        ]);
                    } else {
                        // Safe to promote, yearlevel stays within max
                        $student->update([
                            'yearlevel' => $nextYear,
                            'school_year_id' => $newSY->id
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'School year changed successfully!');
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