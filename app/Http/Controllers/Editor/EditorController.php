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
        $officeusers = User::all();
        $dept = $user->department;
        $title = $user->title;
        $userId = $user->id; // Get the user's ID

        // Start a new query
        $eventsQuery = Event::query();

        // --- APPLY NEW LOGIC ---
        if ($dept === 'OFFICES') {
            // If user is from OFFICES, they only see events they created
            $eventsQuery->where('user_id', $userId);
        } else {
            // Otherwise, they see their department's events + all OFFICE events
            // We also need to eager-load the 'user' relationship for the tag
            $eventsQuery->with('user')->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                ->orWhere('department', 'OFFICES');
            });
        }
        // --- END NEW LOGIC ---

        // Get the results
        $events = $eventsQuery->orderBy('date', 'asc')->get();

        $myEventsCount = $events->count();

        // The rest of your code stays the same
        return view('Editor.dashboard', compact('myEventsCount', 'events', 'title', 'officeusers'));
    }

    public function calendar()
    {
        $events = Event::all()->map(function ($event) {
            return [
                'date' => $event->date,
                'title' => $event->title,
                'description' => $event->description ?? 'No description provided.', // default if null
                'timeStart' => $event->start_time,
                'timeEnd' => $event->end_time,
                'location' => $event->location,
                'sy' => $event->school_year,
                'type' => strtolower(str_replace('/', '_', $event->department ?? 'general')),
                'organizer' => $event->department ?? 'N/A',
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
        return redirect('/');
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