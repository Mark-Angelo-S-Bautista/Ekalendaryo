<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function dashboard()
    {
        return view('UserManagement.dashboard');
    }

    public function calendar()
    {
        return view('UserManagement.calendar');
    }

    public function profile()
    {
        return view('UserManagement.profile');
    }
}