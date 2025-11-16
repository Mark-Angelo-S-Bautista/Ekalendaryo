<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
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

        return view('Editor.dashboard', compact('user', 'events', 'myEventsCount'));
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

        return view('Editor.calendar', ['events' => $events]);
    }

    public function activity_log()
    {
        return view('Editor.activity_log');
    }

    public function history()
    {
        return view('Editor.history');
    }

    public function archive()
    {
        return view('Editor.archive');
    }

    public function profile()
    {
        return view('Editor.profile');
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
            // Create the tag name
            if ($event->department === 'OFFICES' && $event->user) {
                $event->tag_name = $event->user->title;
            } else {
                $event->tag_name = $event->department;
            }

            // Pre-format the time
            $event->formatted_start_time = Carbon::parse($event->start_time)->format('g:i A');
            $event->formatted_end_time = Carbon::parse($event->end_time)->format('g:i A');

            return $event;
        });

        return response()->json(['events' => $events]);
    }
}