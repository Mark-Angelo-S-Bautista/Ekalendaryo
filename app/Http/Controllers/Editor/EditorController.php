<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class EditorController extends Controller
{
    public function dashboard()
    {
        return view('Editor.dashboard');
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
}