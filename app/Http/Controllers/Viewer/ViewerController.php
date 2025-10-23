<?php

namespace App\Http\Controllers\Viewer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewerController extends Controller
{
    public function dashboard()
    {
        return view('Viewer.dashboard');
    }

    public function calendar()
    {
        return view('Viewer.calendar');
    }

    public function profile()
    {
        return view('Viewer.profile');
    }
}