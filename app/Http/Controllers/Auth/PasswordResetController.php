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
    public function requestOtp(Request $request)
    {
        $request->validate([
            'userId' => ['required', 'string']
        ]);

        // Find user by ID number
        $user = User::where('userId', $request->userId)->first();

        if (!$user) {
            return back()->withErrors(['userId' => 'User ID not found.']);
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Save OTP + expiration (5 minutes)
        $user->update([
            'reset_otp' => $otp,
            'reset_otp_expires_at' => now()->addMinutes(5)
        ]);

        // Send email
        Mail::to($user->email)->send(
            new \App\Mail\SendOtpMail($user, $otp)
        );

        // Redirect to OTP verification page
        return redirect()->route('password.otp.verify')
                ->with('success', 'An OTP has been sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6']
        ]);

        // Find user with matching OTP and not expired
        $user = User::where('reset_otp', $request->otp)
            ->where('reset_otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Store user ID in session temporarily
        session(['otp_user' => $user->id]);

        return redirect()->route('password.change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:6']
        ]);

        $userId = session('otp_user');

        if (!$userId) {
            return redirect()->route('Auth.login')->withErrors(['error' => 'Session expired.']);
        }

        $user = User::find($userId);

        $user->update([
            'password' => Hash::make($request->password),
            'reset_otp' => null,
            'reset_otp_expires_at' => null
        ]);

        // Clear session
        session()->forget('otp_user');

        return redirect()->route('Auth.login')->with('success', 'Password successfully updated. You may now log in.');
    }
}
