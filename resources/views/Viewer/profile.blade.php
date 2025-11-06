<x-viewerLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>eKalendaryo — Profile</title>
        @vite(['resources/css/viewer/profile.css', 'resources/js/viewer/profile.js'])

    </head>

    <body>
        <div class="profile_container">
            <h1>Profile</h1>
            <div class="top-right">
                <button id="editProfile" class="btn btn-primary">✏ Edit Profile</button>
                <button id="saveProfile" class="btn btn-primary hidden">💾 Save Changes</button>
            </div>

            <div class="section">
                <h3>Personal Information</h3>
                <div class="card">
                    <div class="row">
                        <div class="input-box"><input type="text" id="adminName" value="SMIS Administrator" disabled>
                        </div>
                        <div class="input-box" style="max-width:200px"><input type="text" id="adminRole"
                                value="SMIS" disabled></div>
                    </div>


                    <div class="employment">
                        <p style="font-weight:600; margin-bottom:8px;">Academic Information</p>
                        <div class="employment-row">
                            <div>
                                <span style="font-weight:300;">Student ID:</span>
                                <span style="margin-left:90px;">MA12345678</span>
                                <span style="margin-left:250px; font-weight:300;">Department:</span>
                                <span style="margin-left:50px;"><span class="badge">BSIS/ACT</span></span>
                            </div>
                        </div>
                        <div class="employment-row">
                            <div>
                                <span style="font-weight:300;">Section:</span>
                                <span style="margin-left:110px;">Section A</span>
                                <span style="margin-left:280px; font-weight:300;">Year:</span>
                                <span style="margin-left:110px;"><span class="badge">2</span></span>
                            </div>
                        </div>
                    </div>


                    <div class="actions hidden" id="editActions">
                        <button class="btn btn-outline" id="cancelEdit">Cancel</button>
                        <button class="btn btn-primary" id="saveEdit">Save Changes</button>
                    </div>
                </div>
            </div>

            <div class="section">
                <h3>Security Settings</h3>

                <!-- EMAIL -->
                <div class="card">
                    <h4>📧 Email Address</h4>
                    <div class="inner-panel">
                        <div>
                            <strong>Current Email</strong>
                            <div class="muted">smis@school.edu</div>
                        </div>
                        <button class="btn btn-outline" id="btnChangeEmail">Change Email</button>
                    </div>

                    <div class="expandable hidden" id="emailForm">
                        <input type="email" placeholder="New email address">
                        <input type="email" placeholder="Confirm new email">
                        <div class="password-wrapper">
                            <input type="password" id="emailPwd" placeholder="Current password to confirm">
                            <button class="eye-btn" type="button"
                                onclick="togglePassword('emailPwd', this)">👁</button>
                        </div>
                        <div class="actions">
                            <button class="btn btn-outline" id="cancelEmail">Cancel</button>
                            <button class="btn btn-primary">Update Email</button>
                        </div>
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="card">
                    <h4>🔐 Password</h4>
                    <div class="inner-panel">
                        <div>
                            <strong>Password</strong>
                            <div class="muted">Last updated: Never recorded</div>
                        </div>
                        <button class="btn btn-outline" id="btnChangePassword">Change Password</button>
                    </div>

                    <div class="expandable hidden" id="passwordForm">
                        <div class="password-wrapper">
                            <input type="password" id="currentPwd" placeholder="Current password">
                            <button class="eye-btn" type="button"
                                onclick="togglePassword('currentPwd', this)">👁</button>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" id="newPwd" placeholder="New password">
                            <button class="eye-btn" type="button" onclick="togglePassword('newPwd', this)">👁</button>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" id="confirmPwd" placeholder="Confirm new password">
                            <button class="eye-btn" type="button"
                                onclick="togglePassword('confirmPwd', this)">👁</button>
                        </div>
                        <div class="actions">
                            <button class="btn btn-outline" id="cancelPassword">Cancel</button>
                            <button class="btn btn-primary">Update Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
</x-viewerLayout>
