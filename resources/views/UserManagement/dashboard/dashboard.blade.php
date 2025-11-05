<x-usermanLayout>
    <x-slot:vite>
        @vite(['resources/css/userman/UserManDashboard.css', 'resources/js/userman/UserManDashboard.js'])
    </x-slot:vite>
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
            <input type="text" class="dashboard_search_input" placeholder="🔍 Search Events" />
            <button class="dashboard_clear_btn">Clear</button>
        </section>

        <!-- Stats -->
        <section class="dashboard_stats">
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>Department Events</h3>
                <p>9</p>
            </div>
            <div class="dashboard_stat_box">
                <h3>SG Events</h3>
                <p>2</p>
            </div>
            <div class="dashboard_stat_box">
                <h3>Sports Events</h3>
                <p>3</p>
            </div>
            <div class="dashboard_stat_box">
                <h3>Admin Events</h3>
                <p>4</p>
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
                <div class="dashboard_event_details">📅 11/6/2025 &nbsp;&nbsp; 🕓 5:45 PM - 5:45 PM &nbsp;&nbsp; 📍 BPC
                    Court</div>
                <div class="dashboard_event_details">adaddsadsad</div>
                <div class="dashboard_event_details">SY.2025-2026</div>
                <div class="dashboard_event_tags">
                    <span class="dashboard_tag dashboard_tag_admin">admin</span>
                    <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                </div>
            </div>
        </section>
    </div>

    <!-- Department Modal -->
    <div class="dashboard_modal" id="dashboard_department_modal">
        <div class="dashboard_modal_content">
            <div class="dashboard_modal_header">
                <h3>📁 Department Events</h3>
                <button class="dashboard_close_btn" id="dashboard_close_modal">&times;</button>
            </div>
            <div class="dashboard_modal_body">
                <p>Event counts by department</p>
                <div class="dashboard_department_grid">
                    <div class="dashboard_department_card">
                        <h4>BSAIS</h4>
                        <p>0 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>BSCA</h4>
                        <p>0 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>BSIS-ACT</h4>
                        <p>6 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>BSOM</h4>
                        <p>3 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>BTVTED</h4>
                        <p>0 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>DHRMT</h4>
                        <p>0 events</p>
                    </div>
                    <div class="dashboard_department_card">
                        <h4>HB</h4>
                        <p>0 events</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-usermanLayout>
