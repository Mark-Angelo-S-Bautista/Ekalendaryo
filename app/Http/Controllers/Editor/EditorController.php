<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Event;
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


        // Fetch all events within 30 days
        $events = Event::whereBetween('date', [$now->toDateString(), $limitDate->toDateString()])
            ->orWhere(function ($q) use ($now) {
                // Include events happening today but not finished
                $q->where('date', $now->toDateString())
                ->where('end_time', '>=', $now->format('H:i:s'));
            })
            ->orderBy('date', 'asc')
            ->get();

        // Filter events according to department logic
        $upcomingEvents = $events->filter(function ($event) use ($dept) {
            // Skip cancelled or completed events
            if (in_array($event->computed_status, ['completed', 'cancelled'])) {
                return false;
            }
            if ($event->department === $dept) {
                return true; // Show events in the same department
            }

            if ($event->department === 'OFFICES') {
                // Decode target_department
                $targetDepartments = $event->target_department;
                if (is_string($targetDepartments)) {
                    $targetDepartments = json_decode($targetDepartments, true) ?? [];
                }
                if (!is_array($targetDepartments)) {
                    $targetDepartments = [];
                }

                return in_array($dept, $targetDepartments); // Show if user's department is targeted
            }

            return false; // Hide everything else
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

        return view('Editor.dashboard', compact('user', 'events', 'myEventsCount', 'currentSchoolYearName'));
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

        return view('Editor.calendar', ['events' => $events]);
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

        // Only fetch events that are actually 'completed'
        $events = Event::withCount('feedbacks')
            ->where('user_id', $userId)
            ->where('status', 'completed') // ✅ filter by actual status column
            ->orderBy('date', 'desc')
            ->get();

        // --------------------------------------------------
        // Manual Pagination
        // --------------------------------------------------
        $perPage = 2;
        $currentPage = request()->get('page', 1);

        $paginatedEvents = new \Illuminate\Pagination\LengthAwarePaginator(
            $events->forPage($currentPage, $perPage),
            $events->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('Editor.history', [
            'events' => $paginatedEvents,
        ]);
    }

    public function getFeedback(Event $event)
    {
       // Paginate feedbacks, e.g., 5 per page
        $feedbacks = $event->feedbacks()->with('user')->orderBy('created_at', 'desc')->paginate(2);

        // Return a Blade view for modal content
        return view('Editor.partials.feedback_modal_content', compact('feedbacks'))->render();
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
}