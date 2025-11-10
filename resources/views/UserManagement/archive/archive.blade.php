<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo Archive</title>
        @vite(['resources/css/userman/archive.css', 'resources/js/userman/archive.js'])
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

                <!-- Subcards beside each other -->
                <div class="subcard-container">
                    <div class="subcard" onclick="openModal('studentsModal')">
                        <h4>👥 Student Records</h4>
                        <p>3 student records from SY.2024-2025</p>
                    </div>

                    <div class="subcard" onclick="openModal('deletedModal')">
                        <h4>🗑️ Recently Deleted</h4>
                        <p>View and restore deleted user records</p>
                    </div>
                </div>
            </div>
        </main>

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
                        <p>ID: BSAIS2024002 | Dept: BSAIS</p>
                    </div>

                    <div class="student" data-dept="BSIS-ACT">
                        <h4>John Williams</h4>
                        <p>ID: BSIS2024001 | Dept: BSIS-ACT</p>
                    </div>

                    <div class="student" data-dept="BSOM">
                        <h4>Michael Davis</h4>
                        <p>ID: BSOM2024003 | Dept: BSOM</p>
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
                    Recently Deleted - User Records
                </div>
                <p>3 of 3 deleted user records</p>

                <div class="search-bar">
                    <input type="text" id="deletedSearch" placeholder="🔍 Search deleted users..."
                        onkeyup="searchDeleted()">
                </div>

                <div class="filter-container">
                    <label for="userType">Filter by:</label>
                    <select id="userType" onchange="toggleUserType()">
                        <option value="all" selected>All Records</option>
                        <option value="student">Student Records</option>
                        <option value="faculty">Faculty Records</option>
                    </select>

                    <select id="deptFilter" onchange="filterDeleted()" style="display:none;">
                        <option value="all">All Departments</option>
                        <option value="BSAIS">BSAIS</option>
                        <option value="BSIS-ACT">BSIS-ACT</option>
                        <option value="BSOM">BSOM</option>
                    </select>
                </div>

                <div id="deletedList" class="eventlist_">
                    <div class="deleted-user" data-type="student" data-dept="BSAIS">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>Jessica Brown</h4>
                        <p>ID: BSAIS2024002 | Dept: BSAIS | Email: former2@school.edu</p>
                    </div>

                    <div class="deleted-user" data-type="student" data-dept="BSIS-ACT">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>John Williams</h4>
                        <p>ID: BSIS2024001 | Dept: BSIS-ACT | Email: former1@school.edu</p>
                    </div>

                    <div class="deleted-user" data-type="faculty" data-dept="CS">
                        <button class="restore-btn" onclick="restoreItem(this)">Restore</button>
                        <h4>Prof. Sarah Johnson</h4>
                        <p>ID: FAC2024001 | Dept: Computer Science | Email: sjohnson@school.edu</p>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
</x-usermanLayout>
