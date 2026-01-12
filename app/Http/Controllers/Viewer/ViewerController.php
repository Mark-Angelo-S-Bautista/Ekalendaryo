<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerifyNewEmail;
use Illuminate\Support\Facades\Mail;
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

        $now = now();

        // Start by getting all events (department filtering will happen later)
        $events = Event::orderBy('date', 'asc')->get();

        $events = $events->filter(function ($ev) use ($now, $yearLevel, $title, $dept) {

            // --- Hide past events ---
            $eventDateTime = \Carbon\Carbon::parse($ev->date . ' ' . $ev->start_time);
            if ($eventDateTime->lt($now)) {
                return false;
            }

            // ============================================================
            // FACULTY LOGIC (Retained for completeness, assuming 'Faculty' title)
            // ============================================================
            if (strtolower($title) === 'faculty') {

                // Convert target_department to array if needed
                $targetDepartments = is_string($ev->target_department)
                    ? json_decode($ev->target_department, true) ?? []
                    : $ev->target_department;
                
                // Normalize target_users for case-insensitive comparison
                $targetUsers = strtolower($ev->target_users ?? '');

                // Faculty can see events if:
                // 1. Event department matches faculty's department
                // OR
                // 2. Event is from OFFICES and targets faculty's department
                // AND
                // 3. Event does NOT target "Department Heads"
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
            // STUDENT LOGIC (assuming 'Viewer' title)
            // ============================================================
            if (strtolower($title) === 'viewer' || strtolower($title) === 'student') { 

                // 🔑 NEW LOGIC: Prevent Students from seeing events targeted at Faculty/Department Heads
                $targetUsersNormalized = strtolower($ev->target_users ?? '');
                
                if (str_contains($targetUsersNormalized, 'faculty') || str_contains($targetUsersNormalized, 'department head')) {
                    return false; // Student is immediately filtered out
                }
                
                // Normalize user year level early
                $userYearLevelNormalized = null;
                if (!empty($yearLevel)) {
                    $userYearLevelNormalized = strtolower(str_replace(' ', '', $yearLevel));
                }

                // -----------------------------
                // Helper: Check Year Level Match
                // -----------------------------
                $matchesYearLevel = function ($ev) use ($userYearLevelNormalized) {

                    // Convert JSON string to array if needed
                    $targetYearLevels = is_string($ev->target_year_levels)
                        ? json_decode($ev->target_year_levels, true) ?? []
                        : $ev->target_year_levels;

                    // If event has no year-level restrictions → allow all students
                    if (empty($targetYearLevels)) {
                        return true;
                    }

                    // If user has no year level → cannot qualify
                    if (empty($userYearLevelNormalized)) {
                        return false;
                    }

                    // Normalize target list
                    $normalizedTargets = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $targetYearLevels);

                    return in_array($userYearLevelNormalized, $normalizedTargets);
                };

                // ===================================================================
                // RULE 1: Student can see events from **their own department**
                // ===================================================================
                if ($ev->department === $dept) {
                    return $matchesYearLevel($ev);
                }

                // ===================================================================
                // RULE 2: Student can see events from **OFFICES** targeting their department
                //         - event.target_department contains user's department
                //         - AND event.target_users == "Students" (or is empty, depending on final requirements)
                //         - AND year level matches
                // ===================================================================
                $targetDepartments = is_string($ev->target_department)
                    ? json_decode($ev->target_department, true) ?? []
                    : $ev->target_department;
                
                $targetUsersCheck = strtolower($ev->target_users ?? '');

                if (
                    $ev->department === 'OFFICES' &&
                    is_array($targetDepartments) &&
                    in_array($dept, $targetDepartments) &&
                    ($targetUsersCheck === 'students' || empty($targetUsersCheck)) // Allow if specifically for students OR if generic/empty target
                ) {
                    return $matchesYearLevel($ev);
                }

                // ===================================================================
                // RULE 3: Future-proof: any event targeting student's department
                //         + target_users == Students
                // ===================================================================
                if (
                    is_array($targetDepartments) &&
                    in_array($dept, $targetDepartments) &&
                    $targetUsersCheck === 'students'
                ) {
                    return $matchesYearLevel($ev);
                }

                // ===================================================================
                // Otherwise: student cannot see the event
                // ===================================================================
                return false;
            }
            
            // ============================================================
            // DEFAULT/OTHER USERS LOGIC (e.g., Department Head, Admin, etc.)
            // ============================================================
            // If the user is neither Faculty nor Student, include specific logic here.
            // For now, we assume if they are a Department Head or Admin, they see everything or have specific rules.
            // If no rules match above, we return true as a temporary default for non-specified roles, 
            // or strictly false if only the above two roles are supported.
            
            // To be safe, let's include all remaining events for non-student/non-faculty users (like Department Head/Admin)
            return true; 


        })->values();


        $myEventsCount = $events->count();

        return view('Viewer.dashboard', compact('myEventsCount', 'events', 'title', 'officeusers'));
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

        return view('Viewer.calendar', ['events' => $events]);
    }

    public function notifications()
    {
        return view('Viewer.notifications');
    }

    public function history()
    {
        return view('Viewer.history');
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
}