<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class ViewerController extends Controller
{
    public function dashboard()
    {
        return view('Viewer.dashboard');
    }

    public function calendar()
    {
        $events = Event::all();
        return view('UserManagement.calendar.calendar', compact('events'));
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
}