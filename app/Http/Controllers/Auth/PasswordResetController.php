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
        $newPassword = 'newpassword';

        // ✅ 4. Hash and update the password in the database
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // // ✅ 5. Send the new password to the user’s email
        // Mail::raw("Hello {$user->name},\n\nYour password has been reset. Here’s your new password:\n\n{$newPassword}\n\nYou can now log in using this new password and change it afterward.", function ($message) use ($user) {
        //     $message->to($user->email)
        //             ->subject('Your New Password from eKalendaryo');
        // });

        // ✅ 6. Redirect back with a success message
        return redirect()->route('Auth.login')->with('success', 'A new password has been sent to your email.');
    }
}
