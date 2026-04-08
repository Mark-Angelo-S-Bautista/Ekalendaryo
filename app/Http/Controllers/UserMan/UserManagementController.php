<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\SchoolYear;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\VerifyNewEmail;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // Fetch all events with attendees
        $events = Event::with('user', 'attendees')->get();

        // Departments
        $departments = Department::pluck('department_name')->toArray();

        $now = now();

        // User targeting variables
        $userTitle = strtolower($user->title ?? '');
        $userDept = $user->department;
        $userSection = $user->section ? strtolower(trim($user->section)) : null;
        $userYearLevel = $user->yearlevel ? strtolower(str_replace(' ', '', $user->yearlevel)) : null;

        /*
        |--------------------------------------------------------------------------
        | FILTER UPCOMING + ONGOING EVENTS (ACTIVE EVENTS) + USER PARTICIPATION
        |--------------------------------------------------------------------------
        */
        $activeEvents = $events->filter(function ($event) use ($now, $user, $userTitle, $userDept, $userSection, $userYearLevel) {

            // ❌ Exclude cancelled & completed
            if (in_array($event->computed_status, ['cancelled', 'completed'])) {
                return false;
            }

            $start = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            $end   = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);

            // Check if event is ongoing or upcoming
            $isOngoing = $start->lessThanOrEqualTo($now) && $end->greaterThanOrEqualTo($now);
            $isUpcoming = $start->greaterThan($now) && $start->lessThanOrEqualTo($now->copy()->addDays(356));

            if (!$isOngoing && !$isUpcoming) {
                return false;
            }

            // Check if user is in target_faculty
            $targetFaculty = is_string($event->target_faculty)
                ? json_decode($event->target_faculty, true) ?? []
                : ($event->target_faculty ?? []);
            
            if (is_array($targetFaculty) && in_array($user->id, $targetFaculty)) {
                return true;
            }

            // Check if user is in target_office_users
            $targetOfficeUsers = is_string($event->target_office_users)
                ? json_decode($event->target_office_users, true) ?? []
                : ($event->target_office_users ?? []);
            
            if (is_array($targetOfficeUsers) && in_array($user->id, $targetOfficeUsers)) {
                return true;
            }

            // For Office users: ONLY show if they're in target_faculty or target_office_users (already checked above)
            if ($userDept === 'OFFICES' || $user->title === 'Offices') {
                return false;
            }

            // For Faculty users
            if ($userTitle === 'faculty') {
                return false; // Faculty only sees events where they're in target_faculty
            }

            // For Students/Viewers: check department, sections, and year levels
            if ($userTitle === 'student' || $userTitle === 'viewer') {
                // Check department match
                $targetDepartments = is_string($event->target_department)
                    ? json_decode($event->target_department, true) ?? []
                    : ($event->target_department ?? []);

                $deptMatch = $event->department === $userDept || 
                    (is_array($targetDepartments) && (in_array('All', $targetDepartments) || in_array($userDept, $targetDepartments)));

                if (!$deptMatch) {
                    return false;
                }

                // Check section match
                $targetSections = is_string($event->target_sections)
                    ? json_decode($event->target_sections, true) ?? []
                    : ($event->target_sections ?? []);
                
                $sectionMatch = empty($targetSections) || 
                    ($userSection && in_array($userSection, array_map('strtolower', array_map('trim', $targetSections))));

                // Check year level match
                $targetYearLevels = is_string($event->target_year_levels)
                    ? json_decode($event->target_year_levels, true) ?? []
                    : ($event->target_year_levels ?? []);

                $yearLevelMatch = empty($targetYearLevels) || 
                    ($userYearLevel && in_array($userYearLevel, array_map(function($lvl) {
                        return strtolower(str_replace(' ', '', $lvl));
                    }, $targetYearLevels)));

                return $sectionMatch && $yearLevelMatch;
            }

            // For Department Heads
            if ($user->title === 'Department Head') {
                $targetUsers = $event->target_users ?? '';
                
                // Check for Department Heads or Faculty & Department Heads
                if (in_array($targetUsers, ['Department Heads', 'Faculty & Department Heads'])) {
                    $targetDepartments = is_string($event->target_department)
                        ? json_decode($event->target_department, true) ?? []
                        : ($event->target_department ?? []);
                    
                    $normalizedTargetDepts = array_map(fn($d) => strtoupper(trim($d)), $targetDepartments);
                    $userDeptNormalized = strtoupper(trim($userDept));
                    
                    if (in_array('All', $targetDepartments) || in_array($userDeptNormalized, $normalizedTargetDepts)) {
                        return true;
                    }
                }
                
                // Faculty target_users also targets Department Heads
                if ($targetUsers === 'Faculty') {
                    $targetDepartments = is_string($event->target_department)
                        ? json_decode($event->target_department, true) ?? []
                        : ($event->target_department ?? []);
                    
                    // Show if same department OR target_department includes user's dept or 'All'
                    if ($event->department === $userDept ||
                        in_array($userDept, $targetDepartments) ||
                        in_array('All', $targetDepartments)) {
                        return true;
                    }
                    return false;
                }
                
                return false;
            }

            // For other user types: check target_users field
            $targetUsers = $event->target_users ?? '';
            if (!empty($targetUsers)) {
                if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                    return true;
                }
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

        // Count per department based on WHO CREATED the active event
        $countableEvents = $events->filter(function ($event) use ($now) {
            if (in_array($event->computed_status, ['cancelled', 'completed'])) {
                return false;
            }

            $start = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            $end = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);

            $isOngoing = $start->lessThanOrEqualTo($now) && $end->greaterThanOrEqualTo($now);
            $isUpcoming = $start->greaterThan($now) && $start->lessThanOrEqualTo($now->copy()->addDays(356));

            return $isOngoing || $isUpcoming;
        });

        $rawDepartmentCounts = [];
        $registrarCount = 0;

        foreach ($countableEvents as $event) {
            $creator = $event->user;
            $creatorTitle = trim((string) ($creator->title ?? ''));
            $creatorDepartment = trim((string) ($creator->department ?? $event->department ?? ''));

            if (strcasecmp($creatorTitle, 'Offices') === 0 || strcasecmp($creatorDepartment, 'OFFICES') === 0) {
                $registrarCount++;
                continue;
            }

            $normalizedCreatorDept = $this->normalizeDepartmentKey($creatorDepartment);
            if ($normalizedCreatorDept === '') {
                continue;
            }

            $rawDepartmentCounts[$normalizedCreatorDept] = ($rawDepartmentCounts[$normalizedCreatorDept] ?? 0) + 1;
        }

        // Build cards from configured departments (excluding OFFICES, represented by Registrar card)
        $finalDeptCounts = collect($departments)
            ->reject(fn ($dept) => strtoupper(trim((string) $dept)) === 'OFFICES')
            ->mapWithKeys(function ($dept) use ($rawDepartmentCounts) {
                $normalizedDept = $this->normalizeDepartmentKey((string) $dept);
                $count = 0;

                if ($normalizedDept !== '') {
                    // Direct match for exact department name
                    $count += $rawDepartmentCounts[$normalizedDept] ?? 0;

                    // If card is combined (e.g., BSIS/ACT), also include split department creators
                    if (str_contains($normalizedDept, '/')) {
                        foreach (explode('/', $normalizedDept) as $part) {
                            $part = trim($part);
                            if ($part !== '') {
                                $count += $rawDepartmentCounts[$part] ?? 0;
                            }
                        }
                    }
                }

                return [$dept => $count];
            });

        $finalDeptCounts['Registrar'] = $registrarCount;

        // Build dashboard cards for department and office event counts with recent events.
        $departmentsWithEvents = [];

        // Department cards (configured departments excluding OFFICES)
        foreach ($departments as $deptName) {
            if (strtoupper(trim((string) $deptName)) === 'OFFICES') {
                continue;
            }

            if ($this->normalizeDepartmentKey((string) $deptName) === 'BSIS/ACT') {
                continue;
            }

            $normalizedCardDept = $this->normalizeDepartmentKey((string) $deptName);
            $departmentEvents = $countableEvents
                ->filter(function ($event) use ($normalizedCardDept) {
                    $creator = $event->user;
                    $creatorTitle = trim((string) ($creator->title ?? ''));
                    $creatorDepartment = trim((string) ($creator->department ?? $event->department ?? ''));

                    // Office-created events belong to office cards.
                    if (strcasecmp($creatorTitle, 'Offices') === 0 || strcasecmp($creatorDepartment, 'OFFICES') === 0) {
                        return false;
                    }

                    $normalizedCreatorDept = $this->normalizeDepartmentKey($creatorDepartment);
                    if ($normalizedCreatorDept === '' || $normalizedCardDept === '') {
                        return false;
                    }

                    if ($normalizedCreatorDept === $normalizedCardDept) {
                        return true;
                    }

                    // Combined card (e.g., BSIS/ACT) should include split creators (BSIS, ACT).
                    if (str_contains($normalizedCardDept, '/')) {
                        $parts = array_map('trim', explode('/', $normalizedCardDept));
                        return in_array($normalizedCreatorDept, $parts, true);
                    }

                    return false;
                })
                ->sortBy(function ($event) {
                    return Carbon::parse($event->date . ' ' . $event->start_time);
                })
                ->values();

            $departmentsWithEvents[] = [
                'name' => $deptName,
                'count' => $departmentEvents->count(),
                'events' => $departmentEvents->take(3),
            ];
        }

        // Office cards (group by office name)
        $officeEventGroups = $countableEvents
            ->filter(function ($event) {
                $creator = $event->user;
                $creatorTitle = trim((string) ($creator->title ?? ''));
                $creatorDepartment = trim((string) ($creator->department ?? $event->department ?? ''));

                return strcasecmp($creatorTitle, 'Offices') === 0 || strcasecmp($creatorDepartment, 'OFFICES') === 0;
            })
            ->groupBy(function ($event) {
                $office = trim((string) optional($event->user)->office_name);
                return $office !== '' ? $office : 'Office';
            });

        foreach ($officeEventGroups as $officeName => $groupedEvents) {
            $sortedOfficeEvents = $groupedEvents
                ->sortBy(function ($event) {
                    return Carbon::parse($event->date . ' ' . $event->start_time);
                })
                ->values();

            $departmentsWithEvents[] = [
                'name' => $officeName,
                'count' => $sortedOfficeEvents->count(),
                'events' => $sortedOfficeEvents->take(3),
            ];
        }

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
            'departmentsWithEvents' => $departmentsWithEvents,
            'upcomingEvents' => $paginatedEvents,
            'office_name' => $office_name,
            'currentSchoolYearName' => $currentSchoolYearName,
        ]);
    }

    private function normalizeDepartmentKey(string $department): string
    {
        $department = strtoupper(trim($department));
        if ($department === '') {
            return '';
        }

        // Normalize spaces around slashes, e.g., "BSIS / ACT" => "BSIS/ACT"
        return preg_replace('/\s*\/\s*/', '/', $department) ?? $department;
    }

    public function dashboardSearch(Request $request)
    {
        $query = $request->input('query', '');
        $now = now();
        $user = Auth::user();
        $userTitle = strtolower($user->title ?? '');
        $userDept = $user->department;
        $userSection = $user->section ? strtolower(trim($user->section)) : null;
        $userYearLevel = $user->yearlevel ? strtolower(str_replace(' ', '', $user->yearlevel)) : null;

        $events = Event::with('user', 'attendees')
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->get()
            ->filter(function ($event) use ($now, $user, $userTitle, $userDept, $userSection, $userYearLevel) {
                // ❌ Exclude cancelled & completed
                if (in_array($event->computed_status, ['cancelled', 'completed'])) {
                    return false;
                }

                $start = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                $end   = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);

                // Check if event is ongoing or upcoming
                $isOngoing = $start->lessThanOrEqualTo($now) && $end->greaterThanOrEqualTo($now);
                $isUpcoming = $start->greaterThan($now) && $start->lessThanOrEqualTo($now->copy()->addDays(30));

                if (!$isOngoing && !$isUpcoming) {
                    return false;
                }

                // Check if user is in target_faculty
                $targetFaculty = is_string($event->target_faculty)
                    ? json_decode($event->target_faculty, true) ?? []
                    : ($event->target_faculty ?? []);
                
                if (is_array($targetFaculty) && in_array($user->id, $targetFaculty)) {
                    return true;
                }

                // Check if user is in target_office_users
                $targetOfficeUsers = is_string($event->target_office_users)
                    ? json_decode($event->target_office_users, true) ?? []
                    : ($event->target_office_users ?? []);
                
                if (is_array($targetOfficeUsers) && in_array($user->id, $targetOfficeUsers)) {
                    return true;
                }

                // For Office users: ONLY show if they're in target_faculty or target_office_users
                if ($userDept === 'OFFICES' || $user->title === 'Offices') {
                    return false;
                }

                // For Faculty users
                if ($userTitle === 'faculty') {
                    return false;
                }

                // For Students/Viewers
                if ($userTitle === 'student' || $userTitle === 'viewer') {
                    $targetDepartments = is_string($event->target_department)
                        ? json_decode($event->target_department, true) ?? []
                        : ($event->target_department ?? []);

                    $deptMatch = $event->department === $userDept || 
                        (is_array($targetDepartments) && (in_array('All', $targetDepartments) || in_array($userDept, $targetDepartments)));

                    if (!$deptMatch) {
                        return false;
                    }

                    $targetSections = is_string($event->target_sections)
                        ? json_decode($event->target_sections, true) ?? []
                        : ($event->target_sections ?? []);
                    
                    $sectionMatch = empty($targetSections) || 
                        ($userSection && in_array($userSection, array_map('strtolower', array_map('trim', $targetSections))));

                    $targetYearLevels = is_string($event->target_year_levels)
                        ? json_decode($event->target_year_levels, true) ?? []
                        : ($event->target_year_levels ?? []);

                    $yearLevelMatch = empty($targetYearLevels) || 
                        ($userYearLevel && in_array($userYearLevel, array_map(function($lvl) {
                            return strtolower(str_replace(' ', '', $lvl));
                        }, $targetYearLevels)));

                    return $sectionMatch && $yearLevelMatch;
                }

                // For Department Heads
                if ($user->title === 'Department Head') {
                    $targetUsers = $event->target_users ?? '';
                    
                    // Check for Department Heads or Faculty & Department Heads
                    if (in_array($targetUsers, ['Department Heads', 'Faculty & Department Heads'])) {
                        $targetDepartments = is_string($event->target_department)
                            ? json_decode($event->target_department, true) ?? []
                            : ($event->target_department ?? []);
                        
                        $normalizedTargetDepts = array_map(fn($d) => strtoupper(trim($d)), $targetDepartments);
                        $userDeptNormalized = strtoupper(trim($userDept));
                        
                        if (in_array('All', $targetDepartments) || in_array($userDeptNormalized, $normalizedTargetDepts)) {
                            return true;
                        }
                    }
                    
                    // Faculty target_users also targets Department Heads
                    if ($targetUsers === 'Faculty') {
                        $targetDepartments = is_string($event->target_department)
                            ? json_decode($event->target_department, true) ?? []
                            : ($event->target_department ?? []);
                        
                        // Show if same department OR target_department includes user's dept or 'All'
                        if ($event->department === $userDept ||
                            in_array($userDept, $targetDepartments) ||
                            in_array('All', $targetDepartments)) {
                            return true;
                        }
                        return false;
                    }
                    
                    return false;
                }

                // For other user types
                $targetUsers = $event->target_users ?? '';
                if (!empty($targetUsers)) {
                    if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                        return true;
                    }
                }

                return false;
            })
            ->sortBy(function ($event) {
                return \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
            })
            ->values();

        // Add is_attending flag for each event
        $userId = $user->id;
        $events = $events->map(function ($event) use ($userId) {
            $event->is_attending = $event->attendees->contains('id', $userId);
            return $event;
        });

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

        $generatedPassword = Str::random(12);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'office_name' => $request->office_name,
            'userId' => $request->userId,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'title' => $request->title,
            'yearlevel' => $request->yearlevel,
            'section' => $request->section,
            'password' => Hash::make($generatedPassword),
            'must_change_password' => true,
            'school_year_id' => $schoolYearId,
        ]);

        Mail::to($user->email)->queue(new UserCredentialsMail([
            'name' => $user->name,
            'userId' => $user->userId,
            'email' => $user->email,
            'department' => $user->department,
            'yearlevel' => $user->yearlevel,
            'section' => $user->section,
            'password' => $generatedPassword,
        ]));

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
            'must_change_password' => false,
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
        $userId = Auth::id();

        // Fetch logs for current user, latest first
        $logs = ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(3);

        return view('UserManagement.activity_log.activity_log', compact('logs'));
    }

    public function history()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $createdSearch = trim((string) request()->query('created_search', ''));
        $invitedSearch = trim((string) request()->query('invited_search', ''));

        // Events created by the user (for report upload)
        $createdEvents = Event::withCount('feedbacks')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('date', 'desc')
            ->get();

        if ($createdSearch !== '') {
            $createdEvents = $createdEvents->filter(function ($event) use ($createdSearch) {
                $searchableText = implode(' ', [
                    $event->title ?? '',
                    $event->description ?? '',
                    $event->department ?? '',
                    $event->location ?? '',
                    $event->school_year ?? '',
                    $event->date ?? '',
                ]);

                return Str::contains(Str::lower($searchableText), Str::lower($createdSearch));
            })->values();
        }

        // Events the user was invited to (for feedback submission)
        $invitedEvents = Event::query()
            ->where('status', 'completed')
            ->where('user_id', '!=', $userId)
            ->orderBy('date', 'desc')
            ->get();

        // Filter invited events based on targeting logic
        $userTitle = strtolower($user->title ?? '');
        $userDept = $user->department;

        $invitedEvents = $invitedEvents->filter(function($event) use ($user, $userTitle, $userDept) {
            // Check if user is in target_faculty
            $targetFaculty = is_string($event->target_faculty)
                ? json_decode($event->target_faculty, true) ?? []
                : ($event->target_faculty ?? []);
            
            if (is_array($targetFaculty) && in_array($user->id, $targetFaculty)) {
                return true;
            }

            // Check if user is in target_office_users
            $targetOfficeUsers = is_string($event->target_office_users)
                ? json_decode($event->target_office_users, true) ?? []
                : ($event->target_office_users ?? []);
            
            if (is_array($targetOfficeUsers) && in_array($user->id, $targetOfficeUsers)) {
                return true;
            }

            // For Office users: ONLY show if they're in target_faculty or target_office_users
            if ($userDept === 'OFFICES' || $user->title === 'Offices') {
                return false;
            }

            // Check if target_users matches user title
            $targetUsers = $event->target_users ?? '';
            if (!empty($targetUsers)) {
                if ($user->title === 'Department Head') {
                    // Check for Department Heads or Faculty & Department Heads
                    if (in_array($targetUsers, ['Department Heads', 'Faculty & Department Heads'])) {
                        $targetDepartments = $event->target_department;
                        if (is_string($targetDepartments)) {
                            $targetDepartments = json_decode($targetDepartments, true) ?? [];
                        }
                        if (!is_array($targetDepartments)) {
                            $targetDepartments = [];
                        }
                        
                        $normalizedTargetDepts = array_map(fn($d) => strtoupper(trim($d)), $targetDepartments);
                        $userDeptNormalized = strtoupper(trim($userDept));
                        
                        if (in_array('All', $targetDepartments) || in_array($userDeptNormalized, $normalizedTargetDepts)) {
                            return true;
                        }
                        return false;
                    }
                    // Faculty target_users also targets Department Heads
                    if ($targetUsers === 'Faculty') {
                        $targetDepartments = $event->target_department;
                        if (is_string($targetDepartments)) {
                            $targetDepartments = json_decode($targetDepartments, true) ?? [];
                        }
                        if (!is_array($targetDepartments)) {
                            $targetDepartments = [];
                        }
                        
                        // Show if same department OR target_department includes user's dept or 'All'
                        if ($event->department === $userDept ||
                            in_array($userDept, $targetDepartments) ||
                            in_array('All', $targetDepartments)) {
                            return true;
                        }
                        return false;
                    }
                    return false;
                }
                
                if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                    return true;
                }
            }

            return false;
        })->values();

        if ($invitedSearch !== '') {
            $invitedEvents = $invitedEvents->filter(function ($event) use ($invitedSearch) {
                $searchableText = implode(' ', [
                    $event->title ?? '',
                    $event->description ?? '',
                    $event->department ?? '',
                    $event->location ?? '',
                    $event->school_year ?? '',
                    $event->date ?? '',
                ]);

                return Str::contains(Str::lower($searchableText), Str::lower($invitedSearch));
            })->values();
        }

        // Paginate created events
        $perPage = 3;
        $currentPage = request()->get('created_page', 1);
        $paginatedCreatedEvents = new LengthAwarePaginator(
            $createdEvents->forPage($currentPage, $perPage),
            $createdEvents->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'created_page'
            ]
        );

        // Paginate invited events
        $invitedPage = request()->get('invited_page', 1);
        $paginatedInvitedEvents = new LengthAwarePaginator(
            $invitedEvents->forPage($invitedPage, $perPage)->values(),
            $invitedEvents->count(),
            $perPage,
            $invitedPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'invited_page'
            ]
        );

        // Get submitted feedback IDs and attended event IDs
        $submittedFeedbackIds = Feedback::where('user_id', $userId)
            ->pluck('event_id')
            ->toArray();

        $attendedEventIds = DB::table('event_attendees')
            ->where('user_id', $userId)
            ->pluck('event_id')
            ->toArray();

        return view('UserManagement.history.history', [
            'createdEvents' => $paginatedCreatedEvents,
            'invitedEvents' => $paginatedInvitedEvents,
            'submittedFeedbackIds' => $submittedFeedbackIds,
            'attendedEventIds' => $attendedEventIds,
            'createdSearch' => $createdSearch,
            'invitedSearch' => $invitedSearch,
        ]);
    }

    public function getFeedback(Event $event)
    {
        $feedbacks = $event->feedbacks()->with('user')->orderBy('created_at', 'desc')->paginate(2);
        $averageRating = $event->feedbacks()->avg('rating');
        return view('UserManagement.partials.feedback_modal_content', compact('feedbacks', 'averageRating'))->render();
    }

    public function storeFeedback(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'rating' => 'required|integer|min:1|max:5',
            'q_satisfaction' => 'required|string',
            'q_organization' => 'required|string',
            'q_relevance' => 'required|string',
            'comment' => 'required|string|max:1000',
        ]);

        $userId = auth()->id();
        $eventId = $request->event_id;

        $existing = Feedback::where('user_id', $userId)
                            ->where('event_id', $eventId)
                            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted feedback for this event.'
            ]);
        }

        Feedback::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'rating' => $request->rating,
            'q_satisfaction' => $request->q_satisfaction,
            'q_organization' => $request->q_organization,
            'q_relevance' => $request->q_relevance,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully!'
        ]);
    }

    public function uploadReport(Request $request, Event $event)
    {
        $request->validate([
            'report' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('report');
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('reports', $filename, 'public');

        $event->update([
            'report_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report uploaded successfully!',
            'downloadUrl' => route('UserManagement.downloadReport', $event->id),
        ]);
    }

    public function removeReport(Event $event)
    {
        if ($event->report_path && Storage::disk('public')->exists($event->report_path)) {
            Storage::disk('public')->delete($event->report_path);
        }

        $event->update(['report_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Report removed successfully.'
        ]);
    }

    public function downloadReport(Event $event)
    {
        if (!$event->report_path || !Storage::disk('public')->exists($event->report_path)) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        return response()->download(storage_path('app/public/' . $event->report_path));
    }

    public function notifications()
    {
        $user = Auth::user();
        
        $lastViewed = $user->notifications_last_viewed_at;
        
        $user->update(['notifications_last_viewed_at' => now()]);
        
        $userTitle = strtolower($user->title ?? '');

        $events = Event::where(function ($query) use ($user, $userTitle) {

                if ($userTitle === 'faculty') {
                    $query->whereRaw("JSON_CONTAINS(target_faculty, ?)", [json_encode((string)$user->id)]);
                } elseif ($userTitle === 'student' || $userTitle === 'viewer') {
                    // Students should see events from their department OR events targeting their department (including 'All')
                    $query->where(function($q) use ($user) {
                        $q->where('department', $user->department)
                          ->orWhereJsonContains('target_department', $user->department)
                          ->orWhereJsonContains('target_department', 'All');
                    });
                } else {
                    // For Department Heads and Office users
                    $query->where(function($q) use ($user) {
                        $q->whereRaw("JSON_CONTAINS(target_faculty, ?)", [json_encode((string)$user->id)])
                          // OR check if user is targeted in target_office_users
                          ->orWhereRaw("JSON_CONTAINS(target_office_users, ?)", [json_encode((string)$user->id)])
                          ->orWhere(function($subQ) use ($user) {
                              $subQ->where('target_users', 'LIKE', '%' . $user->title . '%')
                                   ->orWhere('target_users', $user->title);
                          });
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($userTitle === 'student' || $userTitle === 'viewer') {
            $userSection = $user->section ? strtolower(trim($user->section)) : null;
            $userYearLevel = $user->yearlevel ? strtolower(str_replace(' ', '', $user->yearlevel)) : null;

            $events = $events->filter(function($event) use ($userSection, $userYearLevel) {
                // Exclude events targeted for faculty, department heads, or offices
                $targetUsersNormalized = strtolower($event->target_users ?? '');
                if (str_contains($targetUsersNormalized, 'faculty') || 
                    str_contains($targetUsersNormalized, 'department head') ||
                    str_contains($targetUsersNormalized, 'offices')) {
                    return false;
                }
                
                $targetSections = is_string($event->target_sections)
                    ? json_decode($event->target_sections, true) ?? []
                    : ($event->target_sections ?? []);
                
                $targetYearLevels = is_string($event->target_year_levels)
                    ? json_decode($event->target_year_levels, true) ?? []
                    : ($event->target_year_levels ?? []);

                $sectionMatch = empty($targetSections) || 
                    ($userSection && in_array($userSection, array_map('strtolower', array_map('trim', $targetSections))));

                $yearLevelMatch = empty($targetYearLevels) || 
                    ($userYearLevel && in_array($userYearLevel, array_map(function($lvl) {
                        return strtolower(str_replace(' ', '', $lvl));
                    }, $targetYearLevels)));

                return $sectionMatch && $yearLevelMatch;
            });
        }

        if (!in_array($userTitle, ['faculty', 'student', 'viewer'])) {
            $events = $events->filter(function($event) use ($user) {
                $targetFaculty = is_string($event->target_faculty)
                    ? json_decode($event->target_faculty, true) ?? []
                    : ($event->target_faculty ?? []);
                
                if (is_array($targetFaculty) && in_array($user->id, $targetFaculty)) {
                    return true;
                }

                // Check if user is in target_office_users
                $targetOfficeUsers = is_string($event->target_office_users)
                    ? json_decode($event->target_office_users, true) ?? []
                    : ($event->target_office_users ?? []);
                
                if (is_array($targetOfficeUsers) && in_array($user->id, $targetOfficeUsers)) {
                    return true;
                }

                if ($user->department === 'OFFICES' || $user->title === 'Offices') {
                    return false;
                }

                $targetUsers = $event->target_users ?? '';
                if (!empty($targetUsers)) {
                    if ($user->title === 'Department Head') {
                        // Check for Department Heads or Faculty & Department Heads
                        if (in_array($targetUsers, ['Department Heads', 'Faculty & Department Heads'])) {
                            $targetDepartments = $event->target_department;
                            if (is_string($targetDepartments)) {
                                $targetDepartments = json_decode($targetDepartments, true) ?? [];
                            }
                            if (!is_array($targetDepartments)) {
                                $targetDepartments = [];
                            }
                            
                            $normalizedTargetDepts = array_map(fn($d) => strtoupper(trim($d)), $targetDepartments);
                            $userDeptNormalized = strtoupper(trim($user->department));
                            
                            if (in_array('All', $targetDepartments) || in_array($userDeptNormalized, $normalizedTargetDepts)) {
                                return true;
                            }
                            return false;
                        }
                        // Faculty target_users also targets Department Heads
                        if ($targetUsers === 'Faculty') {
                            $targetDepartments = $event->target_department;
                            if (is_string($targetDepartments)) {
                                $targetDepartments = json_decode($targetDepartments, true) ?? [];
                            }
                            if (!is_array($targetDepartments)) {
                                $targetDepartments = [];
                            }
                            
                            // Show if same department OR target_department includes user's dept or 'All'
                            if ($event->department === $user->department ||
                                in_array($user->department, $targetDepartments) ||
                                in_array('All', $targetDepartments)) {
                                return true;
                            }
                            return false;
                        }
                        return false;
                    }
                    
                    if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        $perPage = 3;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $events = new LengthAwarePaginator(
            $events->forPage($currentPage, $perPage)->values(),
            $events->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('UserManagement.notifications.notifications', compact('events', 'lastViewed'));
    }

    public function eventsArchive(Request $request)
    {
        $userId = Auth::id();
        $schoolYear = $request->get('school_year');
        $search = trim((string) $request->query('search', ''));

        $schoolYears = Event::where('user_id', $userId)
            ->whereIn('status', ['archived', 'cancelled'])
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        $archivedEvents = Event::with('user')
            ->where('user_id', $userId)
            ->whereIn('status', ['archived', 'cancelled'])
            ->when($schoolYear, function ($q) use ($schoolYear) {
                $q->where('school_year', $schoolYear);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('school_year', 'like', "%{$search}%");
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('UserManagement.eventsArchive.eventsArchive', compact(
            'archivedEvents',
            'schoolYears',
            'schoolYear',
            'search'
        ));
    }

    public function attend(Event $event)
    {
        $user = Auth::user();

        if ($event->attendees()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => 'already'], 200);
        }

        $event->attendees()->attach($user->id);

        return response()->json([
            'status' => 'success',
            'attendees_count' => $event->attendees()->count()
        ]);
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
                    'endDate' => $event->end_date ?: $event->date,
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
        $search = trim((string) $request->query('search', ''));

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
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('userId', 'like', "%{$search}%");
                });
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

        return view('UserManagement.archive.archive', compact('archivedUsers', 'titles', 'title', 'schoolYears', 'schoolYear', 'departments', 'department', 'search'));
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