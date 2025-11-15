<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ViewerController extends Controller
{
    public function dashboard()
{
    $user = Auth::user();
    $officeusers = User::all();
    $dept = $user->department;
    $title = $user->title;
    $yearLevel = $user->yearlevel ?? null; // e.g., "1stYear"

    $now = now();

    // Fetch events from user's department or OFFICES
    $events = Event::whereIn('department', [$dept, 'OFFICES'])
        ->orderBy('date', 'asc')
        ->get();

    $events = $events->filter(function ($ev) use ($now, $yearLevel, $title, $dept) {

        // --- Hide past events ---
        $eventDateTime = \Carbon\Carbon::parse($ev->date . ' ' . $ev->start_time);
        if ($eventDateTime->lt($now)) {
            return false;
        }

        // --- If Faculty ---
        if (strtolower($title) === 'faculty') {
            // Show all events from their department or OFFICES events
            return $ev->department === $dept || $ev->department === 'OFFICES';
        }

        // --- If Student ---
        // First check: event must be from their department or OFFICES
        if ($ev->department !== $dept && $ev->department !== 'OFFICES') {
            return false;
        }

        // Second check: verify student's year level is in target_year_levels
        $targetYearLevels = $ev->target_year_levels;
        if (is_string($targetYearLevels)) {
            $targetYearLevels = json_decode($targetYearLevels, true) ?? [];
        }

        // If target_year_levels is empty or null, show the event to all students
        if (empty($targetYearLevels)) {
            return true;
        }

        // Normalize: remove spaces and lowercase for matching
        $targetYearLevelsNormalized = array_map(function ($lvl) {
            return strtolower(str_replace(' ', '', $lvl)); // "1st Year" -> "1styear"
        }, $targetYearLevels);

        // If student has no year level, don't show the event
        if (empty($yearLevel)) {
            return false;
        }

        $userYearLevelNormalized = strtolower(str_replace(' ', '', $yearLevel)); // "1stYear" -> "1styear"

        // Show event only if user's year level is in target_year_levels
        return in_array($userYearLevelNormalized, $targetYearLevelsNormalized);

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
        return view('Viewer.profile');
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