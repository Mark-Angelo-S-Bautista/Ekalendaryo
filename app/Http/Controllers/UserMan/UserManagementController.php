<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    // Method for the Dashboard tab
    public function dashboard()
    {
        // Simply returns the view file for the dashboard
        return view('UserManagement.dashboard');
    }

    // Method for the Calendar tab
    public function calendar()
    {
        // Simply returns the view file for the calendar
        return view('UserManagement.calendar');
    }

    // Method for the Profile tab
    public function profile()
    {
        // Simply returns the view file for the profile
        return view('UserManagement.profile');
    }
}