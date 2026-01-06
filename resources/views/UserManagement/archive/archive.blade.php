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
                    Student Records
                </div>
                <p>{{ $graduatedStudents->total() }} student(s) records</p>

                @if ($graduatedStudents->count() > 0)
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>School Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($graduatedStudents as $index => $student)
                                <tr>
                                    <td>{{ $graduatedStudents->firstItem() + $index }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->department }}</td>
                                    <td>{{ $student->schoolYear->school_year ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="modal-pagination">
                        {{ $graduatedStudents->links() }}
                    </div>
                @else
                    <p>No graduated student records found.</p>
                @endif
            </div>
        </div>

        <!-- Recently Deleted Modal -->
        <div id="deletedModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('deletedModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">🗑️</span>
                    Recently Deleted Users
                </div>
                <p>{{ $recentlyDeleted->total() }} user(s) records</p>

                @if ($recentlyDeleted->count() > 0)
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentlyDeleted as $index => $user)
                                <tr>
                                    <td>{{ $recentlyDeleted->firstItem() + $index }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->department }}</td>
                                    <td>{{ ucfirst($user->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="modal-pagination">
                        {{ $recentlyDeleted->links() }}
                    </div>
                @else
                    <p>No deleted user records found.</p>
                @endif
            </div>
        </div>
    </body>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = "flex";
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = "none";
        }

        // Optional: close modal when clicking outside
        window.onclick = function(event) {
            ["studentsModal", "deletedModal"].forEach((modalId) => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) modal.style.display = "none";
            });
        };
    </script>

    </html>
</x-usermanLayout>
