<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>eKalendaryo — Profile</title>
        @vite(['resources/css/userman/UserManProfile.css', 'resources/js/userman/UserManProfile.js'])
    </head>

    <body>
        <h1>Profile</h1>

        {{-- Edit buttons for personal info --}}
        {{-- <div class="top-right">
            <button id="editProfile" class="btn btn-primary">✏ Edit Profile</button>
            <button id="saveProfile" class="btn btn-primary hidden">💾 Save Changes</button>
        </div> --}}

        {{-- Personal Information --}}
        <div class="section">
            <h3>Personal Information</h3>
            <div class="card">
                <form method="POST" action="{{ route('UserManagement.profile.update') }}" id="profileForm">
                    @csrf
                    <div class="row">
                        <div class="input-box">
                            <input type="text" name="name" id="adminName" value="{{ old('name', $user->name) }}"
                                disabled>
                        </div>
                        <div class="input-box" style="max-width:200px">
                            <input type="text" name="userId" id="UserId"
                                value="{{ old('userId', $user->userId) }}" disabled>
                        </div>
                    </div>
                    <div class="actions hidden" id="editActions">
                        <button type="button" class="btn btn-outline" id="cancelEdit">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>

                <div class="employment">
                    <p style="font-weight:600; margin-bottom:8px;">
                        Employment Information
                    </p>

                    <div class="employment-row">
                        <div>
                            <span style="font-weight:300;">Title</span>
                            <span style="margin-left:90px;">
                                @if ($user->title === 'Offices')
                                    {{ $user->office_name ?? 'Not specified' }}
                                @else
                                    {{ $user->title ?? 'Not specified' }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="employment-row">
                        <div>
                            <span style="font-weight:300;">Department:</span>
                            <span style="margin-left:100px;">
                                @if ($user->department === 'OFFICES')
                                    {{ $user->office_name ?? 'Not specified' }}
                                @else
                                    {{ $user->department ?? 'Not specified' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Settings --}}
        <div class="section">
            <h3>Security Settings</h3>

            {{-- EMAIL --}}
            <div class="card">
                <h4>📧 Email</h4>

                <div class="inner-panel">
                    <div>
                        <strong>Email</strong>
                        <div class="muted">{{ Auth::user()->email }}</div>
                    </div>
                    <button type="button" class="btn btn-outline" id="btnChangeEmail">
                        Change Email
                    </button>
                </div>

                <form method="POST" action="{{ route('UserManagement.profile.updateEmail') }}"
                    class="expandable hidden" id="emailForm">
                    @csrf

                    <div class="password-wrapper">
                        <input type="email" name="new_email" id="newEmail" placeholder="New email" required>
                    </div>
                    <div id="newEmailError" class="error-message"></div>

                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="emailCurrentPwd"
                            placeholder="Current password" required>
                        <button type="button" class="eye-btn" data-toggle-password>
                            👁
                        </button>
                    </div>
                    <div id="emailPasswordError" class="error-message"></div>

                    <div class="actions">
                        <button type="button" class="btn btn-outline" id="cancelEmail">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Update Email
                        </button>
                    </div>
                </form>
            </div>

            {{-- PASSWORD --}}
            <div class="card">
                <h4>🔐 Password</h4>
                <div class="inner-panel">
                    <div>
                        <strong>Password</strong>
                    </div>
                    <button type="button" class="btn btn-outline" id="btnChangePassword">Change Password</button>
                </div>

                <form method="POST" action="{{ route('UserManagement.profile.updatePassword') }}"
                    class="expandable hidden" id="passwordForm">
                    @csrf

                    {{-- Current Password --}}
                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="currentPwd" placeholder="Current password">
                        <button type="button" class="eye-btn" data-toggle-password>
                            👁
                        </button>
                    </div>
                    <div id="currentPasswordError" class="error-message"></div>

                    {{-- New Password --}}
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="newPwd" placeholder="New password">
                        <button type="button" class="eye-btn" data-toggle-password>
                            👁
                        </button>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="password-wrapper">
                        <input type="password" name="new_password_confirmation" id="confirmPwd"
                            placeholder="Confirm new password">
                        <button type="button" class="eye-btn" data-toggle-password>
                            👁
                        </button>
                    </div>
                    <div id="confirmPasswordError" class="error-message"></div>

                    <div class="actions">
                        <button type="button" class="btn btn-outline" id="cancelPassword">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    @if (session('success'))
        <div id="toast" class="toast">{{ session('success') }}</div>
    @endif

    </html>
</x-usermanLayout>
