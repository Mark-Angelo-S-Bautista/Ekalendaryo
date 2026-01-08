<h2>Verify Your New Email</h2>

<p>Click the button below to confirm your new email address.</p>

<a href="{{ route('profile.verifyNewEmail', ['token' => $token]) }}"
    style="padding:10px 15px;background:#2563eb;color:#fff;text-decoration:none;">
    Verify Email
</a>

<p>If you did not request this change, please ignore this email.</p>
