<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Event;
use App\Models\Department;
use App\Models\Feedback;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\SchoolYear;
use Illuminate\Support\Str;
use App\Mail\VerifyNewEmail;
use Carbon\Carbon;
use App\Models\User;

class EditorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $dept = $user->department;
        $now = Carbon::now();
        $limitDate = $now->copy()->addDays(30);

        // Fetch the current school year from the table
        $currentSchoolYear = SchoolYear::where('is_active', 1)->first(); // Assuming you have an 'is_current' column
        $currentSchoolYearName = $currentSchoolYear ? $currentSchoolYear->school_year : 'N/A';

        $upcomingCount = Event::where('user_id', $user->id)
            ->where('status', 'upcoming')
            ->count();

        $ongoingCount = Event::where('user_id', $user->id)
            ->where('status', 'ongoing')
            ->count();

        $completedCount = Event::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $cancelledCount = Event::where('user_id', $user->id)
            ->where('status', 'cancelled')
            ->count();

        // Fetch all events within 30 days
        $events = Event::with('attendees', 'user')
            ->whereBetween('date', [$now->toDateString(), $limitDate->toDateString()])
            ->orWhere(function ($q) use ($now) {
                // Include events happening today but not finished
                $q->where('date', $now->toDateString())
                ->where('end_time', '>=', $now->format('H:i:s'));
            })
            ->orderBy('date', 'asc')
            ->get();

        // Filter events that target the logged-in user
        $upcomingEvents = $events->filter(function ($event) use ($user, $dept) {
            // Skip cancelled or completed events
            if (in_array($event->computed_status, ['completed', 'cancelled'])) {
                return false;
            }

            // Check if user is targeted by target_faculty
            $targetFaculty = $event->target_faculty;
            if (is_string($targetFaculty)) {
                $targetFaculty = json_decode($targetFaculty, true) ?? [];
            }
            if (!is_array($targetFaculty)) {
                $targetFaculty = [];
            }
            
            if (!empty($targetFaculty) && in_array($user->id, $targetFaculty)) {
                return true;
            }

            // Check if user is targeted by target_users
            $targetUsers = $event->target_users;
            if (!empty($targetUsers)) {
                // For Department Heads: only show Faculty events if department matches
                if ($user->title === 'Department Head') {
                    if ($targetUsers === 'Department Heads') {
                        return true;
                    }
                    if ($targetUsers === 'Faculty' && $event->department === $dept) {
                        return true;
                    }
                    // Don't show other Faculty events or Student events to Dept Heads
                    return false;
                }
                
                // For other users (Faculty, Students, etc.)
                if ($targetUsers === 'Faculty' && $user->title !== 'Student') {
                    return true;
                }
                if ($targetUsers === 'Students' && $user->title === 'Student') {
                    return true;
                }
            }

            return false; // Not targeted to this user
        });

        // Transform for frontend if needed
        $upcomingEvents->transform(function ($event) {
            $event->formatted_start_time = Carbon::parse($event->start_time)->format('g:i A');
            $event->formatted_end_time = Carbon::parse($event->end_time)->format('g:i A');
            return $event;
        });

        $totalEvents = $upcomingEvents->count();

        $events = $upcomingEvents; // alias for Blade
        $myEventsCount = $upcomingEvents->count();

        return view('Editor.dashboard', compact(
            'user', 
            'events', 
            'myEventsCount', 
            'currentSchoolYearName',
            'upcomingCount',
            'ongoingCount',
            'completedCount',
            'cancelledCount'));
    }

    public function calendar()
    {
        $departments = Department::orderBy('department_name')->get();

        $events = Event::where('status', '!=', 'cancelled')
            ->get()
            ->map(function ($event) {

                $targetYearLevels = $event->target_year_levels;

                if (is_string($targetYearLevels)) {
                    $targetYearLevels = json_decode($targetYearLevels, true) ?? [];
                } elseif (!is_array($targetYearLevels)) {
                    $targetYearLevels = [];
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
                    'type' => strtolower(str_replace(['/', ' '], '_', $event->department)),
                    'organizer' => $event->department,
                    'targetYearLevels' => $targetYearLevels,
                ];
            });

        return view('Editor.calendar', compact('events', 'departments'));
    }

    public function activity_log()
    {
        $userId = Auth::id();

        // Fetch logs for current editor, latest first
        $logs = ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(3);

        return view('Editor.activity_log', compact('logs'));
    }

    public function history()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // Events created by the user (for report upload)
        $createdEvents = Event::withCount('feedbacks')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('date', 'desc')
            ->get();

        // Events the user was invited to (for feedback submission)
        $invitedEvents = Event::query()
            ->where('status', 'completed')
            ->where('user_id', '!=', $userId) // Not created by the user
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

            // Check if target_users matches user title
            $targetUsers = $event->target_users ?? '';
            if (!empty($targetUsers)) {
                // For Department Heads: only show Faculty events if department matches
                if ($user->title === 'Department Head') {
                    if ($targetUsers === 'Department Heads') {
                        return true;
                    }
                    if ($targetUsers === 'Faculty' && $event->department === $userDept) {
                        return true;
                    }
                    // Don't show other events unless directly targeted
                    return false;
                }
                
                // For other users
                if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                    return true;
                }
            }

            return false;
        });

        // Paginate created events
        $perPage = 2;
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

        return view('Editor.history', [
            'createdEvents' => $paginatedCreatedEvents,
            'invitedEvents' => $paginatedInvitedEvents,
            'submittedFeedbackIds' => $submittedFeedbackIds,
            'attendedEventIds' => $attendedEventIds,
        ]);
    }

    public function getFeedback(Event $event)
    {
        // Paginate feedbacks, e.g., 5 per page
        $feedbacks = $event->feedbacks()->with('user')->orderBy('created_at', 'desc')->paginate(2);

        // Calculate average rating
        $averageRating = $event->feedbacks()->avg('rating');

        // Return a Blade view for modal content
        return view('Editor.partials.feedback_modal_content', compact('feedbacks', 'averageRating'))->render();
    }

    public function archive(Request $request)
    {
        $userId = Auth::id();
        $schoolYear = $request->get('school_year');

        // School years only from user's archived/cancelled events
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
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('Editor.archive', compact(
            'archivedEvents',
            'schoolYears',
            'schoolYear'
        ));
    }

    public function uploadReport(Request $request, Event $event)
    {
        $request->validate([
            'report' => 'required|file|mimes:pdf|max:10240', // only PDF, max 10MB
        ]);

        $file = $request->file('report');

        // Optional: generate unique filename
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('reports', $filename, 'public');

        $event->update([
            'report_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report uploaded successfully!',
            'downloadUrl' => route('Editor.downloadReport', $event->id),
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
        if (!$event->report_path || !\Storage::disk('public')->exists($event->report_path)) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        return response()->download(storage_path('app/public/' . $event->report_path));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('Editor.profile', compact('user'));
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
            ->route('Editor.profile')
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
        $userId = $user->id;

        // Start a new query
        $eventsQuery = Event::query();

        // --- APPLY THE SAME LOGIC AS THE DASHBOARD ---
        if ($dept === 'OFFICES') {
            // If user is from OFFICES, they only see events they created
            $eventsQuery->where('user_id', $userId);
        } else {
            // Otherwise, they see their department's events + all OFFICE events
            $eventsQuery->with('user')->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                ->orWhere('department', 'OFFICES');
            });
        }
        // --- END NEW LOGIC ---

        // Now, add the search condition to the query
        $eventsQuery->when($query, function ($q) use ($query) {
            $q->where(function ($inner) use ($query) {
                $inner->where('title', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        });

        // Get the results
        $events = $eventsQuery->orderBy('date', 'asc')->get();

        // --- Add this data transformation ---
        // This makes sure your JavaScript gets the correct tag name and time formats
        $events->transform(function ($event) {
            if ($event->department === 'OFFICES' && $event->user) {
                $event->tag_name = $event->user->title;
            } else {
                $event->tag_name = $event->department;
            }

            $event->formatted_start_time = Carbon::parse($event->start_time)->format('g:i A');
            $event->formatted_end_time = Carbon::parse($event->end_time)->format('g:i A');

            // ✅ FIX
            $event->status = $event->computed_status;

            return $event;
        });

        return response()->json(['events' => $events]);
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
    public function notifications()
    {
        $user = Auth::user();
        $userTitle = strtolower($user->title ?? '');

        // Fetch all events regardless of status (including completed, cancelled, updated)
        $events = Event::where(function ($query) use ($user, $userTitle) {

                if ($userTitle === 'faculty') {
                    // Faculty should see events where they are specifically targeted
                    $query->whereRaw("JSON_CONTAINS(target_faculty, ?)", [json_encode((string)$user->id)]);
                } elseif ($userTitle === 'student' || $userTitle === 'viewer') {
                    // Students should see events targeting their section and year level
                    $query->where(function($q) use ($user) {
                        $q->where('department', $user->department)
                          ->orWhereJsonContains('target_department', $user->department);
                    });
                } else {
                    // For Department Heads and Office users
                    $query->where(function($q) use ($user) {
                        // Check if user is specifically targeted in target_faculty
                        $q->whereRaw("JSON_CONTAINS(target_faculty, ?)", [json_encode((string)$user->id)])
                          // OR check if target_users matches their title
                          ->orWhere(function($subQ) use ($user) {
                              $subQ->where('target_users', 'LIKE', '%' . $user->title . '%')
                                   ->orWhere('target_users', $user->title);
                          });
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

        // Additional filter for Department Heads and Office users to verify target_faculty and target_users
        if (!in_array($userTitle, ['faculty', 'student', 'viewer'])) {
            $events = $events->filter(function($event) use ($user) {
                // Check if user ID is in target_faculty
                $targetFaculty = is_string($event->target_faculty)
                    ? json_decode($event->target_faculty, true) ?? []
                    : ($event->target_faculty ?? []);
                
                if (is_array($targetFaculty) && in_array($user->id, $targetFaculty)) {
                    return true;
                }

                // Check if target_users matches user title
                $targetUsers = $event->target_users ?? '';
                if (!empty($targetUsers)) {
                    // For Department Heads: only show Faculty events if department matches
                    if ($user->title === 'Department Head') {
                        if ($targetUsers === 'Department Heads') {
                            return true;
                        }
                        if ($targetUsers === 'Faculty' && $event->department === $user->department) {
                            return true;
                        }
                        // Don't show other events to Dept Heads unless directly targeted
                        return false;
                    }
                    
                    // For other office users
                    if ($targetUsers === $user->title || stripos($targetUsers, $user->title) !== false) {
                        return true;
                    }
                }

                return false;
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

        return view('Editor.notifications', compact('events'));
    }
}