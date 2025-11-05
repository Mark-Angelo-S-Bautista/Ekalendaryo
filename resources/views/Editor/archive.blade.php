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

                    <div class="subcard" onclick="openModal('studentsModal')">
                        <h4>👥 Student Records</h4>
                        <p>3 student records from SY.2024-2025</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Events Modal -->
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

        <!-- Student Records Modal -->
        <div id="studentsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('studentsModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">🎓</span>
                    Student Records - SY.2024-2025
                </div>
                <p>3 of 3 student records</p>

                <label for="studentFilter">Filter by Department: </label>
                <select id="studentFilter" onchange="filterStudents()">
                    <option value="all">All Departments</option>
                    <option value="BSAIS">BSAIS</option>
                    <option value="BSIS-ACT">BSIS-ACT</option>
                    <option value="BSOM">BSOM</option>
                </select>

                <div id="studentList">
                    <div class="student" data-dept="BSAIS">
                        <h4>Jessica Brown</h4>
                        <p>ID: BSAIS2024002 | Dept: BSAIS | Section: B</p>
                        <p>Email: former2@school.edu | DOB: 12/3/2002</p>
                        <p>📞 +1-234-567-8914</p>
                    </div>

                    <div class="student" data-dept="BSIS-ACT">
                        <h4>John Williams</h4>
                        <p>ID: BSIS2024001 | Dept: BSIS-ACT | Section: A</p>
                        <p>Email: former1@school.edu | DOB: 8/14/2002</p>
                        <p>📞 +1-234-567-8913</p>
                    </div>

                    <div class="student" data-dept="BSOM">
                        <h4>Michael Davis</h4>
                        <p>ID: BSOM2024003 | Dept: BSOM | Section: C</p>
                        <p>Email: former3@school.edu | DOB: 6/18/2002</p>
                        <p>📞 +1-234-567-8915</p>
                    </div>
                </div>
            </div>
        </div>

    </body>

    </html>
</x-editorLayout>
