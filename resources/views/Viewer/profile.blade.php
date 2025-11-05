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
        <main class="page" id="page">
            <h1 class="page-title">Profile</h1>

            <div class="floating-action">
                <button id="btnTopEdit" class="btn btn-edit"><span>✏</span> Edit Profile</button>
                <button id="btnTopSave" class="btn btn-save-top" style="display:none"><span>💾</span> Save
                    Changes</button>
            </div>

            <div>
                <div class="section-title">Personal Information</div>
                <div class="card" id="personalCard">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="width:100%;">
                            <div class="personal-row">
                                <div class="label">Personal Details</div>
                                <div>
                                    <div class="input-pill disabled" id="pill1">
                                        <input id="inputName" type="text" value="SMIS Administrator" disabled
                                            aria-label="name">
                                    </div>
                                    <div class="input-pill disabled" id="pill2" style="max-width:220px;">
                                        <input id="inputRole" type="text" value="SMIS" disabled aria-label="role">
                                    </div>

                                    <div class="employment">
                                        <p style="font-weight:600; margin-bottom:8px;">Academic Information</p>
                                        <div class="employment-row">
                                            <div>
                                                <span style="font-weight:300;">Student ID:</span>
                                                <span style="margin-left:90px;">MA12345678</span>
                                                <span style="margin-left:250px; font-weight:300;">Department:</span>
                                                <span style="margin-left:50px;"><span
                                                        class="badge">BSIS/ACT</span></span>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions" id="inlineActions" style="display:none">
                        <button class="btn-cancel" id="btnCancel">Cancel</button>
                        <button class="btn-save" id="btnSave">Save Changes</button>
                    </div>
                </div>
            </div>

            <div class="section-title" style="margin-top:18px">Security Settings</div>

            <div class="card">
                <h4>✉️ Email Address</h4>
                <div class="inner-panel" style="margin-top:6px;">
                    <div>
                        <div style="font-weight:800;color:#164a2a">Current Email</div>
                        <div class="muted-tiny">smis@school.edu</div>
                    </div>
                    <div>
                        <button class="change-btn">Change Email</button>
                    </div>
                </div>
            </div>

            <div class="card">
                <h4>🔐 Password</h4>
                <div class="inner-panel" style="margin-top:6px;">
                    <div>
                        <div style="font-weight:800;color:#164a2a">Password</div>
                        <div class="muted-tiny">Last updated: Never recorded</div>
                    </div>
                    <div>
                        <button class="change-btn">Change Password</button>
                    </div>
                </div>
            </div>

        </main>

    </body>

    </html>
</x-viewerLayout>
