<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;
use App\Models\Feedback;
use App\Models\Department;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerifyNewEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\SchoolYear;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewerController
{
    public function dashboard()
    {
        $user = Auth::user();
        $officeusers = User::all();
        $dept = $user->department;
        $title = $user->title;
        $yearLevel = $user->yearlevel ?? null;

        // Fetch the current school year
        $currentSchoolYear = SchoolYear::where('is_active', 1)->first();
        $currentSchoolYearName = $currentSchoolYear ? $currentSchoolYear->school_year : 'N/A';

        $now = now();

        // Fetch events directly from DB, excluding cancelled and completed
        $events = Event::with('attendees', 'user')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('date', 'asc')
            ->get();

        // Then apply your existing role and department filtering
        $events = $events->filter(function ($ev) use ($now, $yearLevel, $title, $dept, $user) {

            // Hide past events based on date & start_time
            if ($ev->status !== 'ongoing') {
                $eventDateTime = \Carbon\Carbon::parse($ev->date . ' ' . $ev->start_time);
                if ($eventDateTime->lt($now)) {
                    return false;
                }
            }

            // ============================================================
            // FACULTY LOGIC
            // ============================================================
            if (strtolower($title) === 'faculty') {
                
                // Check if this faculty member is specifically targeted
                $targetFaculty = is_string($ev->target_faculty)
                    ? json_decode($ev->target_faculty, true) ?? []
                    : ($ev->target_faculty ?? []);
                
                // Faculty should see events where they are specifically targeted
                $isFacultyTargeted = is_array($targetFaculty) && in_array($user->id, $targetFaculty);

                // OR if target_users contains "Faculty" and department matches
                $targetUsersNormalized = strtolower($ev->target_users ?? '');
                $isGeneralFaculty = str_contains($targetUsersNormalized, 'faculty') && 
                                   ($ev->department === $dept);

                return $isFacultyTargeted || $isGeneralFaculty;
            }

            // ============================================================
            // STUDENT / VIEWER LOGIC
            // ============================================================
            if (strtolower($title) === 'viewer' || strtolower($title) === 'student') {

                $targetUsersNormalized = strtolower($ev->target_users ?? '');

                if (str_contains($targetUsersNormalized, 'faculty') || str_contains($targetUsersNormalized, 'department head')) {
                    return false;
                }

                $userYearLevelNormalized = !empty($yearLevel) ? strtolower(str_replace(' ', '', $yearLevel)) : null;

                $matchesYearLevel = function ($ev) use ($userYearLevelNormalized) {

                    $targetYearLevels = is_string($ev->target_year_levels)
                        ? json_decode($ev->target_year_levels, true) ?? []
                        : $ev->target_year_levels;

                    if (empty($targetYearLevels)) return true;
                    if (empty($userYearLevelNormalized)) return false;

                    $normalizedTargets = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $targetYearLevels);
                    return in_array($userYearLevelNormalized, $normalizedTargets);
                };

                $targetDepartments = is_string($ev->target_department)
                    ? json_decode($ev->target_department, true) ?? []
                    : $ev->target_department;

                $targetUsersCheck = strtolower($ev->target_users ?? '');

                // Rule 1: Events from user's department
                if ($ev->department === $dept) {
                    return $matchesYearLevel($ev);
                }

                // Rule 2: Events from OFFICES targeting student's department
                if ($ev->department === 'OFFICES' &&
                    is_array($targetDepartments) &&
                    in_array($dept, $targetDepartments) &&
                    ($targetUsersCheck === 'students' || empty($targetUsersCheck))
                ) {
                    return $matchesYearLevel($ev);
                }

                // Rule 3: Any event targeting student's department + Students
                if (is_array($targetDepartments) && in_array($dept, $targetDepartments) && $targetUsersCheck === 'students') {
                    return $matchesYearLevel($ev);
                }

                return false;
            }

            // ============================================================
            // OTHER USERS / DEFAULT LOGIC
            // ============================================================
            return true;

        })->values();

        $myEventsCount = $events->count();

        return view('Viewer.dashboard', compact(
            'myEventsCount',
            'events',
            'title',
            'officeusers',
            'currentSchoolYearName'
        ));
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

        return view('Viewer.calendar', compact('events', 'departments', 'user', 'officeNames'));
    }

    public function notifications()
    {
        $user = Auth::user();
        
        // Store the last viewed timestamp BEFORE updating (for highlighting new items)
        $lastViewed = $user->notifications_last_viewed_at;
        
        // Update the last viewed timestamp to reset notification count
        $user->update(['notifications_last_viewed_at' => now()]);
        
        $userTitle = strtolower($user->title ?? '');

        $events = Event::where(function ($query) use ($user, $userTitle) {

                if ($userTitle === 'faculty') {
                    // Faculty should see events where:
                    // 1. They are specifically targeted in target_faculty OR
                    // 2. target_users contains "Faculty" AND department matches
                    $query->where(function($q) use ($user) {
                        $q->whereRaw("JSON_CONTAINS(target_faculty, ?)", [json_encode((string)$user->id)])
                          ->orWhere(function($subQ) use ($user) {
                              $subQ->where('target_users', 'LIKE', '%Faculty%')
                                   ->where('department', $user->department);
                          });
                    });
                } elseif ($userTitle === 'student' || $userTitle === 'viewer') {
                    // Students should see events targeting their section and year level
                    $query->where(function($q) use ($user) {
                        $q->where('department', $user->department)
                          ->orWhereJsonContains('target_department', $user->department);
                    });
                } else {
                    // For other users
                    $query->where('department', $user->department)
                        ->orWhereJsonContains('target_department', $user->department)
                        ->orWhere(function ($q) use ($user) {
                            $q->whereNotNull('target_year_levels')
                            ->whereJsonContains('target_year_levels', $user->yearlevel);
                        });
                }
            })
            // Order by most recently updated first, fallback to created_at
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'asc')
            ->get();

        // Filter students by section and year level
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

                // Check section match
                $sectionMatch = empty($targetSections) || 
                    ($userSection && in_array($userSection, array_map('strtolower', array_map('trim', $targetSections))));

                // Check year level match
                $yearLevelMatch = empty($targetYearLevels) || 
                    ($userYearLevel && in_array($userYearLevel, array_map(function($lvl) {
                        return strtolower(str_replace(' ', '', $lvl));
                    }, $targetYearLevels)));

                return $sectionMatch && $yearLevelMatch;
            });
        }

        // Paginate manually
        $perPage = 3;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $events = new LengthAwarePaginator(
            $events->forPage($currentPage, $perPage)->values(),
            $events->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('Viewer.notifications', compact('events', 'lastViewed'));
    }

    public function history()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Normalize user year level (students only)
        $userYearLevel = $user->yearlevel
            ? preg_replace('/(\d)(st|nd|rd|th)Year/', '$1$2 Year', $user->yearlevel)
            : null;

        $events = Event::query()
            ->where('status', 'completed')
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('date', 'desc')
            ->get();

        $userDept = $user->department;
        $userTitle = strtolower($user->title ?? '');
        $userSection = $user->section ? strtolower(trim($user->section)) : null;
        $userYearLevelNormalized = $userYearLevel
            ? strtolower(str_replace(' ', '', $userYearLevel))
            : null;

        $filtered = $events->filter(function ($event) use ($userDept, $userTitle, $userSection, $userYearLevelNormalized) {
            $targetUsers = strtolower($event->target_users ?? '');

            $targetDepartments = is_string($event->target_department)
                ? json_decode($event->target_department, true) ?? []
                : $event->target_department;

            $targetYearLevels = is_string($event->target_year_levels)
                ? json_decode($event->target_year_levels, true) ?? []
                : $event->target_year_levels;

            $targetSections = is_string($event->target_sections)
                ? json_decode($event->target_sections, true) ?? []
                : $event->target_sections;

            $matchesYearLevel = function () use ($targetYearLevels, $userYearLevelNormalized) {
                if (empty($targetYearLevels)) return true;
                if (empty($userYearLevelNormalized)) return false;
                $normalizedTargets = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $targetYearLevels);
                return in_array($userYearLevelNormalized, $normalizedTargets);
            };

                $matchesSection = function () use ($targetSections, $userSection) {
                    if (empty($targetSections)) return true;
                    if (empty($userSection)) return false;
                    $normalizedTargets = array_map(fn($sec) => strtolower(trim($sec)), $targetSections);
                    return in_array($userSection, $normalizedTargets);
                };

                if ($userTitle === 'faculty') {
                    // Check if this faculty member is specifically targeted in target_faculty
                    $targetFaculty = is_string($event->target_faculty)
                        ? json_decode($event->target_faculty, true) ?? []
                        : ($event->target_faculty ?? []);
                    
                    if (is_array($targetFaculty) && in_array(auth()->id(), $targetFaculty)) {
                        return true;
                    }
                    
                    // Also check if target_users is "Faculty" and department matches
                    if ($targetUsers === 'faculty' && $event->department === $userDept) {
                        return true;
                    }
                    
                    return false;
                }

                if ($userTitle === 'viewer' || $userTitle === 'student') {
                    if (str_contains($targetUsers, 'faculty') || str_contains($targetUsers, 'department head')) {
                        return false;
                    }

                    $matchesTargets = $matchesYearLevel() && $matchesSection();

                    if ($event->department === $userDept) {
                        return $matchesTargets;
                    }

                    if ($event->department === 'OFFICES' &&
                        is_array($targetDepartments) &&
                        in_array($userDept, $targetDepartments) &&
                        ($targetUsers === 'students' || empty($targetUsers))
                    ) {
                        return $matchesTargets;
                    }

                    if (is_array($targetDepartments) && in_array($userDept, $targetDepartments) && $targetUsers === 'students') {
                        return $matchesTargets;
                    }

                    return false;
                }

                return true;
            })->values();

            $perPage = 3;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $pageItems = $filtered->forPage($currentPage, $perPage)->values();

            $events = new LengthAwarePaginator(
                $pageItems,
                $filtered->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            $submittedFeedbackIds = Feedback::where('user_id', $user->id)
                ->pluck('event_id')
                ->toArray();

            // Get events the user has attended
            $attendedEventIds = \DB::table('event_attendees')
                ->where('user_id', $user->id)
                ->pluck('event_id')
                ->toArray();

        return view('Viewer.history', compact('events', 'submittedFeedbackIds', 'attendedEventIds'));
    }


    public function storeFeedback(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'comment' => 'required|string|max:1000',
        ]);

        $userId = auth()->id();
        $eventId = $request->event_id;

        // Check if feedback already exists
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
            'message' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully!'
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('Viewer.profile', compact('user'));
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
            ->route('Viewer.profile')
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

    public function destroy(Request $request)
    {
        Auth::logout();

        // Invalidate the current session
        $request->session()->invalidate();

        // Regenerate the CSRF token for the next request
        $request->session()->regenerateToken();

        // Redirect the user back to the homepage or login page
        return redirect()->route('login');
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $user = Auth::user();
        $dept = $user->department;

        $events = Event::where(function ($q) use ($dept) {
                $q->where('department', $dept)
                ->orWhere('department', 'OFFICES');
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['events' => $events]);
    }

    public function attend(Event $event)
    {
        $user = Auth::user();

        // Make sure relationship uses your custom pivot table
        if ($event->attendees()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => 'already'], 200);
        }

        $event->attendees()->attach($user->id);

        return response()->json([
            'status' => 'success',
            'attendees_count' => $event->attendees()->count()
        ]);
    }
}