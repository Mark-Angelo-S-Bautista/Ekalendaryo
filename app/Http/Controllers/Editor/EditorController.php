<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    // Method for the Dashboard tab
    public function dashboard()
    {
        // Simply returns the view file for the dashboard
        return view('Editor.dashboard');
    }

    // Method for the Calendar tab
    public function calendar()
    {
        // Simply returns the view file for the calendar
        return view('Editor.calendar');
    }

    // Method for the Profile tab
    public function profile()
    {
        // Simply returns the view file for the profile
        return view('Editor.profile');
    }
}