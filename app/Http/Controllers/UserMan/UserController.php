<?php

namespace App\Http\Controllers\UserMan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function adduser(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'userId' => 'required|string|unique:users,userId',
            'email' => 'required|email|unique:users,email',
            'phoneNum' => 'nullable|string|max:20',
            'role' => 'required|in:Editor,UserManagement,Viewer', // adjust roles as needed
        ]);

         User::create([
            'name' => $request->name,
            'userId' => $request->userId,
            'email' => $request->email,
            'phoneNum' => $request->phoneNum,
            'role' => $request->role,
            'password' => Hash::make('password'), // hash the password
        ]);

        // 3. Redirect back with success message
        return redirect()->route('UserManagement.users')->with('success', 'User added successfully!');

    }
}