<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>eKalendaryo — Profile</title>
        @vite(['resources/css/editor/profile.css', 'resources/js/editor/profile.js'])
    </head>

    <body>
        <h1>Profile</h1>

        {{-- Edit buttons for personal info --}}
        <div class="top-right">
            <button id="editProfile" class="btn btn-primary">✏ Edit Profile</button>
            <button id="saveProfile" class="btn btn-primary hidden">💾 Save Changes</button>
        </div>

        {{-- Personal Information --}}
        <div class="section">
            <h3>Personal Information</h3>
            <div class="card">
                <form method="POST" action="{{ route('Editor.editor.profile.update') }}" id="profileForm">
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
                    <p style="font-weight:600; margin-bottom:8px;">Employment Information</p>
                    <div class="employment-row">
                        <div>
                            <span style="font-weight:300;">Title</span>
                            <span style="margin-left:90px;">Department Head</span>
                        </div>
                    </div>
                    <div class="employment-row">
                        <div>
                            <span style="font-weight:300;">Department:</span>
                            <span style="margin-left:100px;">BSIS-ACT</span>
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
                <h4>📧 Email Address</h4>
                <div class="inner-panel">
                    <div>
                        <strong>Current Email</strong>
                        <div class="muted">{{ $user->email }}</div>
                    </div>
                    <button type="button" class="btn btn-outline" id="btnChangeEmail">Change Email</button>
                </div>

                <form method="POST" action="{{ route('Editor.editor.profile.updateEmail') }}" class="expandable hidden"
                    id="emailForm">
                    @csrf
                    <input type="email" name="email" placeholder="New email address">
                    <input type="email" name="email_confirmation" placeholder="Confirm new email">
                    <div class="password-wrapper">
                        <input type="password" name="password" id="emailPwd" placeholder="Current password to confirm">
                        <button class="eye-btn" type="button" onclick="togglePassword('emailPwd', this)">👁</button>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-outline" id="cancelEmail">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Email</button>
                    </div>
                </form>
            </div>

            {{-- PASSWORD --}}
            <div class="card">
                <h4>🔐 Password</h4>
                <div class="inner-panel">
                    <div>
                        <strong>Password</strong>
                        <div class="muted">Last updated: {{ $user->password_last_updated ?? 'Never recorded' }}</div>
                    </div>
                    <button type="button" class="btn btn-outline" id="btnChangePassword">Change Password</button>
                </div>

                <form method="POST" action="{{ route('Editor.editor.profile.updatePassword') }}"
                    class="expandable hidden" id="passwordForm">
                    @csrf
                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="currentPwd" placeholder="Current password"
                            required>
                        <button class="eye-btn" type="button" onclick="togglePassword('currentPwd', this)">👁</button>
                    </div>

                    <!-- Error message for current password -->
                    <div id="currentPasswordError" class="error-message"></div>

                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="newPwd" placeholder="New password" required>
                        <button class="eye-btn" type="button" onclick="togglePassword('newPwd', this)">👁</button>
                    </div>

                    <div class="password-wrapper">
                        <input type="password" name="new_password_confirmation" id="confirmPwd"
                            placeholder="Confirm new password" required>
                        <button class="eye-btn" type="button"
                            onclick="togglePassword('confirmPwd', this)">👁</button>
                    </div>

                    <div class="actions">
                        <button type="button" class="btn btn-outline" id="cancelPassword">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>

                    {{-- place for errors --}}
                    <div id="passwordErrors" class="error-message"></div>
                </form>
            </div>
        </div>
    </body>
    @if (session('success'))
        <div id="toast" class="toast">{{ session('success') }}</div>
    @endif

    </html>
</x-editorLayout>
