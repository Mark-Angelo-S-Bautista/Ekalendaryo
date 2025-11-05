<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>eKalendaryo - Event History</title>
        @vite(['resources/css/userman/history.css', 'resources/js/userman/history.js'])
    </head>

    <body>
        <div class="container">
            <h2>Event History</h2>

            <div class="filter-bar">
                <input type="text" id="search" placeholder="🔍 Search events...">
                <select id="filterType">
                    <option value="All Types" selected>All Types</option>
                    <option value="Department">Department</option>
                    <option value="Student Government">Student Government</option>
                    <option value="Sports">Sports</option>
                    <option value="Admin">Admin</option>
                </select>
                <button onclick="clearFilters()">Clear Filters</button>
            </div>

            <div id="eventList">
                <!-- Events -->
                <div class="event-card" data-type="Department">
                    <div class="event-header">
                        <h3>Annual Science Fair</h3>
                        <span class="tag bsis-act">BSIS-ACT</span>
                        <span class="status">completed</span>
                    </div>
                    <p class="event-details">Students present their science projects and research findings</p>
                    <div class="event-meta">
                        <span>📅 8/15/2025</span>
                        <span>⏰ 09:00 - 15:00</span>
                        <span>📍 Main Hall</span>
                        <span>👤 Science Department</span>
                        <span>🕒 SY.2025-2026</span>
                        <span>👥 3 attendees</span>
                        <span>💬 1 feedback</span>
                    </div>
                </div>

                <div class="event-card" data-type="Admin">
                    <div class="event-header">
                        <h3>Welcome Orientation</h3>
                        <span class="tag admin">Admin</span>
                        <span class="status">completed</span>
                    </div>
                    <p class="event-details">Orientation program for new students and faculty members</p>
                    <div class="event-meta">
                        <span>📅 8/1/2025</span>
                        <span>⏰ 08:00 - 12:00</span>
                        <span>📍 University Auditorium</span>
                        <span>👤 Administration</span>
                        <span>🕒 SY.2025-2026</span>
                        <span>👥 6 attendees</span>
                        <span>💬 0 feedback</span>
                    </div>
                </div>

                <div class="event-card" data-type="Sports">
                    <div class="event-header">
                        <h3>Football Championship</h3>
                        <span class="tag sports">Sports</span>
                        <span class="status">completed</span>
                    </div>
                    <p class="event-details">Final match of the inter-university football championship</p>
                    <div class="event-meta">
                        <span>📅 7/20/2025</span>
                        <span>⏰ 16:00 - 18:00</span>
                        <span>📍 Sports Complex</span>
                        <span>👤 Sports Department</span>
                        <span>🕒 SY.2025-2026</span>
                        <span>👥 3 attendees</span>
                        <span>💬 2 feedback</span>
                    </div>
                </div>
            </div>

            <div class="footer-box">
                <span>📊</span>
                <span>Showing 11 of 11 events</span>
            </div>
        </div>

    </body>

    </html>
</x-usermanLayout>
