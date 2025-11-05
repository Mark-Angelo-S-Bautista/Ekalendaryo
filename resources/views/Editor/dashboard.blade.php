<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo - Admin Dashboard</title>
        <link rel="stylesheet" href="../assets/css/editor_dashboard.css">
    </head>

    <body>
        <div class="dashboard_container">
            <!-- Welcome -->
            <section class="dashboard_welcome_card">
                <div>
                    <h2>Welcome back, SMIS!</h2>
                    <p>Admin Dashboard</p>
                    <p class="dashboard_school_year">Current School Year: SY.2025-2026</p>
                </div>
                <button class="dashboard_change_year_btn">Change School Year</button>
            </section>

            <!-- Search -->
            <section class="dashboard_search_card">
                <input type="text" class="dashboard_search_input" placeholder="Search Events" />
                <button class="dashboard_clear_btn">Clear</button>
            </section>

            <!-- Stats -->
            <section class="dashboard_stats">
                <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                    <h3>My Events</h3>
                    <p>9</p>
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="dashboard_upcoming_card">
                <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
                <p>Next 2 upcoming events (within 30 days)</p>

                <div class="dashboard_event_card">
                    <div class="dashboard_event_title">dsad</div>
                    <div class="dashboard_event_details">📅 11/5/2025 &nbsp;&nbsp; 🕓 3:55 PM - 3:55 PM &nbsp;&nbsp; 📍
                        COURT</div>
                    <div class="dashboard_event_details">adaddsds</div>
                    <div class="dashboard_event_details">SY.2025-2026</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">admin</span>
                        <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                    </div>
                </div>

                <div class="dashboard_event_card">
                    <div class="dashboard_event_title">dsadasdasdad</div>
                    <div class="dashboard_event_details">📅 11/6/2025 &nbsp;&nbsp; 🕓 5:45 PM - 5:45 PM &nbsp;&nbsp; 📍
                        BPC Court</div>
                    <div class="dashboard_event_details">adaddsadsad</div>
                    <div class="dashboard_event_details">SY.2025-2026</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">admin</span>
                        <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                    </div>
                </div>
            </section>
        </div>


    </body>

    </html>
</x-editorLayout>
