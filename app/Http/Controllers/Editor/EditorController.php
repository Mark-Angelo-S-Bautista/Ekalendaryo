<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditorController extends Controller
{
    public function dashboard()
    {
        return view('Editor.dashboard');
    }

    public function calendar()
    {
        return view('Editor.calendar');
    }

    public function manageEvents()
    {
        return view('Editor.manageEvents');
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