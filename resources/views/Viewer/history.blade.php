<x-viewerLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>eKalendaryo - Event History</title>
        @vite(['resources/css/viewer/history.css', 'resources/js/viewer/history.js'])

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
                <!-- (your existing event cards, unchanged) -->
                <div class="event-card" data-type="Department" data-title="Annual Science Fair">
                    <button class="feedback-btn">💬 Feedback</button>
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

                <div class="event-card" data-type="Admin" data-title="Welcome Orientation">
                    <button class="feedback-btn">💬 Feedback</button>
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

                <div class="event-card" data-type="Sports" data-title="Football Championship">
                    <button class="feedback-btn">💬 Feedback</button>
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

                <div class="event-card" data-type="Feedback" data-title="Student1 Feedback Example">
                    <button class="feedback-btn">💬 Feedback</button>
                    <div class="event-header">
                        <h3>Feedback by student1</h3>
                        <span class="tag bsis-act">BSIS-ACT</span>
                        <span class="status">submitted</span>
                    </div>
                    <p class="event-details">"This is an example of previous feedback text."</p>
                    <div class="event-meta">
                        <span>👤 student1</span>
                        <span>🕒 11/6/2025, 7:06:02 AM</span>
                        <span>💬 1 feedback</span>
                    </div>
                </div>
            </div>

            <div class="footer-box">
                <span>📊</span>
                <span>Showing 12 of 12 events</span>
            </div>
        </div>

        <!-- Feedback Modal -->
        <div class="modal-overlay" id="feedbackModal">
            <div class="modal">
                <button class="close-btn" id="closeModal">&times;</button>
                <h3 id="modalTitle">Leave Feedback</h3>
                <p>Share your thoughts and feedback about this completed event</p>

                <div class="modal-section" id="modalEventInfo">
                    <span class="tag bsis-act" style="color:white;">BSIS-ACT</span>
                    <span class="status">completed</span>
                    <span>📅 8/15/2025</span>
                    <span>⏰ 09:00 - 15:00</span>
                    <span>📍 Main Hall</span>
                </div>

                <div class="modal-section">
                    <strong>Previous Feedback</strong>

                    <!-- Feedback card added inside modal -->
                    <div class="feedback-card">
                        <div class="feedback-card-header">
                            <span><strong>student1</strong></span>
                            <small>11/6/2025, 7:06:02 AM</small>
                        </div>
                        <p>This is an example of previous feedback text.</p>
                    </div>
                </div>

                <div>
                    <strong>Your Feedback</strong>
                    <textarea rows="3"
                        placeholder="How was the event? Share your experience, suggestions for improvement, or any other feedback..."></textarea>
                    <button class="submit-feedback">Submit Feedback</button>
                </div>
            </div>
        </div>
    </body>

    </html>

</x-viewerLayout>
