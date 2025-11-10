<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>eKalendaryo Archive</title>
        @vite(['resources/css/editor/archive.css', 'resources/js/editor/archive.js'])
    </head>

    <body>

        <main>
            <h2>Archive</h2>
            <p>Past school year events and records</p>

            <div class="card">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span>▾</span>
                    <div>
                        <strong>SY. 2024-2025</strong>
                        <p style="margin: 0; font-size: 14px; color: gray;">5 events • 3 students</p>
                    </div>
                </div>

                <div class="row">
                    <div class="subcard" onclick="openModal('eventsModal')">
                        <h4>📅 Events Archive</h4>
                        <p>5 completed events from SY.2024-2025</p>
                    </div>

                    <!-- ✅ Added Recently Deleted card -->
                    <div class="subcard" onclick="openModal('deletedModal')">
                        <h4>🗑️ Recently Deleted</h4>
                        <p>View and restore deleted items</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Events Modal (Unchanged) -->
        <div id="eventsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('eventsModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">📆</span>
                    Events Archive - SY.2024-2025
                </div>
                <p>5 of 5 events</p>

                <label for="eventFilter">Filter by Event Type: </label>
                <select id="eventFilter" onchange="filterEvents()">
                    <option value="all">All Event Types</option>
                    <option value="department">Department Events</option>
                    <option value="student">Student Government Events</option>
                    <option value="sports">Sports Events</option>
                    <option value="admin">Admin Events</option>
                </select>

                <div id="eventList" class="eventlist_">
                    <div class="event" data-type="admin">
                        <h4>Annual Awards Ceremony 2024 <span class="tag">admin</span></h4>
                        <p>Recognition ceremony for outstanding students and faculty</p>
                        <p>📍 Main Auditorium | ⏰ March 15, 2025 (2PM - 5PM)</p>
                        <p>👤 Organizer: Admin | 🧍‍♂️ 5 attendees</p>
                    </div>

                    <div class="event" data-type="department">
                        <h4>Engineering Department Research Symposium 2024 <span class="tag">department</span></h4>
                        <p>Presentation of student and faculty research projects</p>
                        <p>📍 Engineering Building Auditorium | ⏰ December 10, 2024 (10AM - 3PM)</p>
                        <p>👤 Organizer: Dr. David Martinez | 🧍‍♂️ 3 attendees</p>
                    </div>

                    <div class="event" data-type="student">
                        <h4>SG Leadership Summit 2024 <span class="tag">student</span></h4>
                        <p>Student leadership and planning summit</p>
                        <p>📍 Conference Center | ⏰ November 20, 2024 (9AM - 4PM)</p>
                        <p>👤 Organizer: Prof. Michael Johnson | 🧍‍♂️ 3 attendees</p>
                    </div>

                    <div class="event" data-type="sports">
                        <h4>Basketball Championship Finals 2024 <span class="tag">sports</span></h4>
                        <p>🏀 Sports event finals</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Recently Deleted Modal -->
        <div id="deletedModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('deletedModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">🗑️</span>
                    Recently Deleted - SY.2024-2025
                </div>
                <p>3 deleted events</p>

                <div id="deletedList" class="eventlist_">
                    <div class="event">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>Orientation Day 2024 <span class="tag">student</span></h4>
                        <p>Freshmen orientation event</p>
                        <p>📍 Gymnasium | ⏰ August 12, 2024 (8AM - 12PM)</p>
                        <p>👤 Organizer: SG Office | 🧍‍♂️ 4 attendees</p>
                    </div>

                    <div class="event">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>Cleanup Drive 2024 <span class="tag">department</span></h4>
                        <p>Environmental awareness activity</p>
                        <p>📍 Campus Grounds | ⏰ July 25, 2024 (9AM - 11AM)</p>
                        <p>👤 Organizer: Department of Environment | 🧍‍♂️ 12 attendees</p>
                    </div>

                    <div class="event">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>Alumni Meet 2024 <span class="tag">admin</span></h4>
                        <p>Gathering of alumni and admin officials</p>
                        <p>📍 Main Hall | ⏰ May 30, 2024 (6PM - 9PM)</p>
                        <p>👤 Organizer: Admin Office | 🧍‍♂️ 8 attendees</p>
                    </div>
                </div>
            </div>
        </div>

    </body>

    </html>
</x-editorLayout>
