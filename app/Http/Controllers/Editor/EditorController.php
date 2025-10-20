<?php

namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function profile()
    {
        return view('Editor.profile');
    }
}