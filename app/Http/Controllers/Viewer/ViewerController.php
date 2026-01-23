<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;
use App\Models\Feedback;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerifyNewEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Log;

class ViewerController extends Controller
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
        $events = Event::whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('date', 'asc')
            ->get();

        // Then apply your existing role and department filtering
        $events = $events->filter(function ($ev) use ($now, $yearLevel, $title, $dept) {

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

                $targetDepartments = is_string($ev->target_department)
                    ? json_decode($ev->target_department, true) ?? []
                    : $ev->target_department;

                $targetUsers = strtolower($ev->target_users ?? '');

                if (
                    $targetUsers !== 'department heads' && (
                        $ev->department === $dept ||
                        ($ev->department === 'OFFICES' && is_array($targetDepartments) && in_array($dept, $targetDepartments))
                    )
                ) {
                    return true;
                }

                return false;
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
        // Fetch only events that are NOT cancelled
        $events = Event::where('status', '!=', 'cancelled')
            ->get()
            ->map(function ($event) {

                // --- SANITIZE target_year_levels ---
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
                    'type' => strtolower(str_replace(['/', ' '], '_', $event->department ?? 'general')),
                    'organizer' => $event->department ?? 'N/A',
                    'targetYearLevels' => $targetYearLevels,
                ];
            });

        return view('Viewer.calendar', ['events' => $events]);
    }

    public function notifications()
    {
        $user = Auth::user();

        $events = Event::where(function ($query) use ($user) {

                // Same department
                $query->where('department', $user->department)

                    // OR targeted department
                    ->orWhereJsonContains('target_department', $user->department)

                    // OR targeted year levels
                    ->orWhere(function ($q) use ($user) {
                        $q->whereNotNull('target_year_levels')
                        ->whereJsonContains('target_year_levels', $user->yearlevel);
                    });

            })
            // Order by most recently updated first, fallback to created_at
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'asc')
            ->paginate(3); // Adjust pagination as needed

        return view('Viewer.notifications', compact('events'));
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

        $query = Event::query()
            ->where('status', 'completed')
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($user) {
                $q->where('department', $user->department)
                ->orWhere('department', 'ALL');
            });

        $events = $query->orderBy('date', 'desc')->paginate(3);

        $events->getCollection()->transform(function ($event) use ($userYearLevel) {
            if (!$userYearLevel) return $event; // faculty

            $targets = is_string($event->target_year_levels)
                ? json_decode($event->target_year_levels, true) ?? []
                : $event->target_year_levels;

            if (empty($targets) || in_array('ALL', $targets)) {
                return $event;
            }

            return in_array($userYearLevel, $targets) ? $event : null;
        });

        $events->setCollection(
            $events->getCollection()->filter()
        );

        $submittedFeedbackIds = Feedback::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();

        return view('Viewer.history', compact('events', 'submittedFeedbackIds'));
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