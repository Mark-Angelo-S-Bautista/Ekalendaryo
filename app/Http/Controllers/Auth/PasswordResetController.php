<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        // ✅ 1. Validate inputs
        $request->validate([
            'userId' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        // ✅ 2. Check if user exists with given ID and email
        $user = User::where('userId', $request->userId)
                    ->where('email', $request->email)
                    ->first();

        if (!$user) {
            return back()->withErrors(['userId' => 'No account found matching these credentials.']);
        }

        // ✅ 3. Generate a new random password
        $newPassword = Str::random(10);

        // ✅ 4. Hash and update the password in the database
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        Mail::to($user->email)->send(
            new \App\Mail\NewPassword($user, $newPassword)
        );

        // ✅ 6. Redirect back with a success message
        return redirect()->route('Auth.login')->with('success', 'A new password has been sent to your email.');
    }
}
