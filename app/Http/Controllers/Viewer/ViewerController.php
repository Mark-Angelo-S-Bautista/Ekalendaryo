<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;

class ViewerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $officeusers = User::all();
        $dept = $user->department;
        $title = $user->title;

        // fetch events that belong to user's department OR OFFICES
        $events = Event::where('department', $dept)
            ->orWhere('department', 'OFFICES')
            ->orderBy('date', 'asc')
            ->get();

        $myEventsCount = $events->count();

        // // upcoming within 30 days (future events)
        // $upcomingEvents = $events->filter(function ($ev) {
        //     $evDate = Carbon::parse($ev->date);
        //     return $evDate->isFuture() && $evDate->diffInDays(Carbon::now()) <= 30;
        // })->values()->take(2); // ->values() to reindex the collection

        return view('Viewer.dashboard', compact('myEventsCount', 'events', 'title', 'officeusers'));
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
                'type' => strtolower(str_replace(['/', ' '], '_', $event->department ?? 'general')),
                'organizer' => $event->department ?? 'N/A',
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
        return redirect('/');
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