<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        @vite(['resources/css/editor/manageEvents.css', 'resources/js/editor/manageEvents.js'])
    </head>

    <body>
        <div id="manage_event" class="tab-content">
            <div class="event-container">
                <header>
                    <h1>Event Management</h1>
                    <p>Create and manage events for your organization</p>
                    <button class="create-btn" id="openModalBtn">+ Create Event</button>
                </header>

                <!-- Create Event POP UP -->
                <div class="modal-overlay" id="modalOverlay">
                    <div class="modal">
                        <h2>Create New Event</h2>
                        <p>Fill in the event details to create a new event</p>

                        <!-- Create Event FORM -->
                        <form action="{{ route('Editor.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>Event title</label>
                                <input type="text" id="eventTitle" name="title" placeholder="Event title" required>
                            </div>

                            <div class="form-group">
                                <label>Event description</label>
                                <input type="text" id="eventDescription" name="description"
                                    placeholder="Event Description" required>
                            </div>

                            <div class="form-group">
                                <button type="button" id="openDetailsModalBtn" class="btn-secondary">
                                    ➕ Add More Details
                                </button>
                            </div>

                            <!-- Hidden input to store More Details -->
                            <input type="hidden" id="moreDetailsInput" name="more_details">

                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" id="eventDate" name="date" min="{{ now()->format('Y-m-d') }}"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" id="startTime" name="start_time" min="07:00" max="17:00"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" id="endTime" name="end_time" min="07:00" max="17:00"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="eventLocation">Event Location</label>
                                <select id="eventLocation" name="location" onchange="toggleOtherLocation()"
                                    class="form-control" required>
                                    <option value="">-- Select a location --</option>
                                    <option value="Covered Court">Covered Court</option>
                                    <option value="Activity Center">Activity Center</option>
                                    <option value="Library">Library</option>
                                    <option value="Audio Visual Room">Audio Visual Room</option>
                                    <option value="Auditorium">Auditorium</option>
                                    <option value="Other">Other</option>
                                </select>

                                <!-- Input for custom location -->
                                <input type="text" id="otherLocation" name="other_location"
                                    placeholder="Please specify location" class="form-control"
                                    style="display: none; margin-top: 10px;" required>
                            </div>

                            <script>
                                function toggleOtherLocation() {
                                    const select = document.getElementById('eventLocation');
                                    const otherInput = document.getElementById('otherLocation');

                                    if (select.value === 'Other') {
                                        otherInput.style.display = 'block';
                                        otherInput.required = true;
                                    } else {
                                        otherInput.style.display = 'none';
                                        otherInput.required = false;
                                        otherInput.value = '';
                                    }
                                }
                            </script>

                            @if (auth()->user()->title === 'Offices' || auth()->user()->title === 'Department Head')
                                <div class="form-group">
                                    <label for="targetDepartment">Target Department</label>

                                    @php
                                        // Get the authenticated user's data for comparison
$user = Auth::user();
$userTitle = $user->title ?? null;
// Assuming the user's department name is stored in 'department_name' or falls back to 'department'
                                        $userDepartment = $user->department_name ?? ($user->department ?? null);
                                    @endphp

                                    <div class="form-group">


                                        @if ($userTitle === 'Department Head')
                                            {{-- 🔑 FIX: If the user is Department Head, render a hidden field with their department as the value. --}}
                                            <input type="hidden" name="target_department[]"
                                                value="{{ $userDepartment }}">
                                            <p style="color: #6c757d; margin-top: 5px;">
                                                Targeting is set to your department:
                                                <strong>{{ $userDepartment }}</strong> (Fixed)
                                            </p>
                                        @else
                                            {{-- If the user is NOT a Department Head (e.g., OFFICES or other), show the standard checkbox grid --}}
                                            <div class="checkbox-grid">
                                                @foreach ($departments as $dept)
                                                    @php
                                                        $departmentName = $dept->department_name;
                                                        $shouldRender = true; // Assume true unless excluded

                                                        if ($userTitle === 'Offices') {
                                                            // If user title is OFFICES, show all departments EXCEPT 'OFFICES'
                                                            if ($departmentName === 'OFFICES') {
                                                                $shouldRender = false;
                                                            }
                                                        }
                                                        // For other titles, you can add more specific rules here if needed.
                                                    @endphp

                                                    @if ($shouldRender)
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" class="dept-checkbox"
                                                                name="target_department[]"
                                                                value="{{ $departmentName }}">
                                                            <span>{{ $departmentName }}</span>
                                                        </label>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label for="targetUsers">Target Users</label>
                                    <select id="targetUsers" name="target_users" class="form-control">
                                        <option value="">-- Select Users --</option>
                                        <option value="Faculty">Faculty</option>
                                        @if (auth()->user()->title === 'Offices')
                                            <option value="Department Heads">Department Heads</option>
                                        @endif
                                        <option value="Students">Students</option>
                                    </select>
                                </div>
                            @endif

                            @if (auth()->user()->title !== 'Offices' || 'Department Head')
                                <!-- Target Year Levels checkboxes -->
                                <div class="form-group" id="targetYearLevelsContainer">
                                    <label>Target Year Levels</label>
                                    <p class="note">Select which year levels of students will receive notifications
                                        for this event</p>

                                    <div class="checkbox_select">
                                        <div class="checkbox-inline">
                                            <input type="checkbox" id="select_all_create">
                                            <label for="select_all_create">Select All Year Levels</label>
                                        </div>
                                    </div>

                                    <div class="checkbox-group" id="yearLevelsContainer">
                                        @php
                                            $maxYear =
                                                auth()->user()->title === 'Offices'
                                                    ? $userMaxYearLevels
                                                    : $userMaxYearLevels;
                                            $yearOptions = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
                                            $yearNumbers = [1, 2, 3, 4, 5];
                                        @endphp
                                        @foreach ($yearOptions as $index => $year)
                                            @if ($yearNumbers[$index] <= $userMaxYearLevels)
                                                <div class="checkbox-inline">
                                                    <input type="checkbox" name="target_year_levels[]"
                                                        value="{{ $year }}" class="syear">
                                                    {{ $year }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Section Button -->
                                <div class="form-group" style="margin-top: 10px;">
                                    <button type="button" id="openSectionModalBtn" class="btn btn-secondary"
                                        style="display:none;">➕ Select Section</button>
                                </div>

                                <!-- Section Modal -->
                                <div id="sectionModalOverlay" class="custom-modal-overlay">
                                    <div class="custom-modal">
                                        <h3>Select Section</h3>

                                        <!-- Select All -->
                                        <div class="checkbox-select-all">
                                            <input type="checkbox" id="selectAllSections"><strong> Select All</strong>
                                        </div>

                                        <!-- Section checkboxes -->
                                        <div class="checkbox-grid" id="sectionsContainer">
                                            @foreach ($sections as $section)
                                                <label class="checkbox-item">
                                                    <input type="checkbox" name="target_sections[]"
                                                        value="{{ $section }}">
                                                    <span>{{ $section }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="modal-buttons">
                                            <button type="button" id="closeSectionModalBtn"
                                                class="btn-cancel">Close</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="form-group" style="margin-top: 10px;">
                                    <button type="button" id="openFacultyModalBtn" class="btn btn-secondary">➕
                                        Select
                                        Faculty</button>
                                </div>

                                <!-- Faculty Modal -->
                                <div id="facultyModalOverlay" class="custom-modal-overlay">
                                    <div class="custom-modal">
                                        <h3>Select Faculty</h3>
                                        <div class="checkbox-grid">
                                            @foreach ($faculty as $f)
                                                <label class="checkbox-item">
                                                    <input type="checkbox" name="target_faculty[]"
                                                        value="{{ $f->id }}">
                                                    <span>{{ $f->name }} ({{ $f->department }})</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="modal-buttons">
                                            <button type="button" id="closeFacultyModalBtn"
                                                class="btn btn-cancel">Close</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="button-group">
                                <button type="button" class="btn-cancel" id="closeModalBtn">Cancel</button>
                                <button type="submit" class="btn-create">Create Event</button>
                            </div>
                            <div id="sectionFacultyValidationError"
                                style="color: red; margin-top: 10px; font-weight: 500; display: none;"></div>
                        </form>
                        <!-- JS for Faculty & Section toggling -->
                        <script>
                            window.sectionsByDepartment = @json($sectionsByDepartment ?? []);
                            window.userTitle = @json(auth()->user()->title);
                            window.departmentMaxYearLevels = @json($departmentMaxYearLevels ?? []);
                            window.userMaxYearLevels = @json($userMaxYearLevels ?? 4);
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                // ==============================
                                // Dynamic Year Levels for Offices
                                // ==============================
                                const deptCheckboxes = document.querySelectorAll('.dept-checkbox');
                                const yearLevelsContainer = document.getElementById('yearLevelsContainer');

                                function toNumberYearLevel(value) {
                                    if (typeof value === 'number') return value;
                                    if (!value) return 0;
                                    const match = String(value).match(/\d+/);
                                    return match ? parseInt(match[0], 10) : 0;
                                }

                                function updateYearLevels() {
                                    if (window.userTitle !== 'Offices') return;

                                    // Get selected departments
                                    const selectedDepts = [...deptCheckboxes]
                                        .filter(cb => cb.checked)
                                        .map(cb => cb.value);

                                    // Determine max year level
                                    let maxYearLevel = toNumberYearLevel(window.userMaxYearLevels) || 4;

                                    if (selectedDepts.length > 0) {
                                        // Get max year level from selected departments (use MAXIMUM)
                                        maxYearLevel = 0;
                                        selectedDepts.forEach(dept => {
                                            const deptMaxRaw = window.departmentMaxYearLevels[dept];
                                            const deptMax = toNumberYearLevel(deptMaxRaw) || 4;
                                            maxYearLevel = Math.max(maxYearLevel, deptMax);
                                        });
                                    }

                                    renderYearLevels(maxYearLevel);
                                }

                                function renderYearLevels(maxYear) {
                                    if (!yearLevelsContainer) return;

                                    const yearOptions = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
                                    const yearNumbers = [1, 2, 3, 4, 5];

                                    yearLevelsContainer.innerHTML = '';

                                    yearNumbers.forEach((num, index) => {
                                        if (num <= maxYear) {
                                            const div = document.createElement('div');
                                            div.className = 'checkbox-inline';

                                            const input = document.createElement('input');
                                            input.type = 'checkbox';
                                            input.name = 'target_year_levels[]';
                                            input.value = yearOptions[index];
                                            input.className = 'syear';

                                            const label = document.createElement('label');
                                            label.textContent = yearOptions[index];
                                            label.style.display = 'inline';
                                            label.style.marginLeft = '5px';

                                            div.appendChild(input);
                                            div.appendChild(label);
                                            yearLevelsContainer.appendChild(div);
                                        }
                                    });

                                    // Re-wire select all logic
                                    wireSelectAllLogic();
                                }

                                // Listen to department checkbox changes
                                deptCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', updateYearLevels);
                                });

                                // ==============================
                                // Select All Year Levels Logic
                                // ==============================
                                const selectAllCheckbox = document.getElementById('select_all_create');

                                function wireSelectAllLogic() {
                                    const yearCheckboxes = document.querySelectorAll('.syear');

                                    if (selectAllCheckbox) {
                                        selectAllCheckbox.checked = false;
                                        selectAllCheckbox.onchange = null;
                                        selectAllCheckbox.addEventListener('change', () => {
                                            yearCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                                        });
                                    }

                                    yearCheckboxes.forEach(cb => {
                                        cb.addEventListener('change', () => {
                                            if (![...yearCheckboxes].every(cb => cb.checked)) {
                                                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                                            } else {
                                                if (selectAllCheckbox) selectAllCheckbox.checked = true;
                                            }
                                        });
                                    });
                                }

                                // Initial setup
                                if (window.userTitle === 'Offices') {
                                    // For Offices users, dynamically render year levels
                                    updateYearLevels();
                                } else {
                                    // For other users, just wire the existing checkboxes
                                    wireSelectAllLogic();
                                }

                                // ==============================
                                // Target Users logic for Section & Faculty buttons
                                // ==============================
                                const targetUsers = document.getElementById('targetUsers');
                                const openSectionBtn = document.getElementById('openSectionModalBtn');
                                const openFacultyBtn = document.getElementById('openFacultyModalBtn');

                                function toggleButtons() {
                                    if (targetUsers.value === 'Students') {
                                        openSectionBtn.style.display = 'inline-block';
                                        openFacultyBtn.style.display = 'inline-block';
                                    } else {
                                        openSectionBtn.style.display = 'none';
                                        openFacultyBtn.style.display = 'none';
                                    }
                                }

                                targetUsers.addEventListener('change', toggleButtons);
                                toggleButtons(); // initial check on page load

                                // ==============================
                                // Faculty Modal Logic
                                // ==============================
                                const facultyModal = document.getElementById('facultyModalOverlay');
                                const closeFacultyBtn = document.getElementById('closeFacultyModalBtn');

                                openFacultyBtn.addEventListener('click', () => {
                                    facultyModal.classList.add('active');
                                });
                                closeFacultyBtn.addEventListener('click', () => {
                                    facultyModal.classList.remove('active');
                                });

                                // ==============================
                                // Section Modal Logic
                                // ==============================
                                const sectionModal = document.getElementById('sectionModalOverlay');
                                const closeSectionBtn = document.getElementById('closeSectionModalBtn');

                                openSectionBtn.addEventListener('click', () => {
                                    sectionModal.classList.add('active');
                                });
                                closeSectionBtn.addEventListener('click', () => {
                                    sectionModal.classList.remove('active');
                                });

                                // ==============================
                                // Section list filtering (Offices)
                                // ==============================
                                const sectionsByDepartment = window.sectionsByDepartment || {};
                                const sectionsContainer = document.getElementById('sectionsContainer');
                                const deptCheckboxesForSections = document.querySelectorAll('.dept-checkbox');

                                function getSelectedDepartments() {
                                    return [...deptCheckboxesForSections]
                                        .filter(cb => cb.checked)
                                        .map(cb => cb.value);
                                }

                                function collectSectionsForDepartments(departments) {
                                    if (departments.includes('All')) {
                                        return Object.values(sectionsByDepartment).flat();
                                    }

                                    const collected = departments.flatMap(dept => sectionsByDepartment[dept] || []);
                                    return [...new Set(collected)];
                                }

                                function renderSections(sections) {
                                    if (!sectionsContainer) return;

                                    sectionsContainer.innerHTML = '';

                                    if (!sections.length) {
                                        const emptyLabel = document.createElement('div');
                                        emptyLabel.style.color = '#6c757d';
                                        emptyLabel.style.fontStyle = 'italic';
                                        emptyLabel.textContent = 'Select a target department to load sections.';
                                        sectionsContainer.appendChild(emptyLabel);
                                        return;
                                    }

                                    sections.forEach(section => {
                                        const label = document.createElement('label');
                                        label.className = 'checkbox-item';

                                        const input = document.createElement('input');
                                        input.type = 'checkbox';
                                        input.name = 'target_sections[]';
                                        input.value = section;

                                        const span = document.createElement('span');
                                        span.textContent = section;

                                        label.appendChild(input);
                                        label.appendChild(span);
                                        sectionsContainer.appendChild(label);
                                    });
                                }

                                function updateSectionsForOffices() {
                                    if (window.userTitle !== 'Offices') return;
                                    const selectedDepartments = getSelectedDepartments();
                                    const sections = collectSectionsForDepartments(selectedDepartments);
                                    renderSections(sections);
                                    wireSectionSelectAll();
                                }

                                // ==============================
                                // Section Modal "Select All" Logic
                                // ==============================
                                const selectAllSections = document.getElementById('selectAllSections');

                                function wireSectionSelectAll() {
                                    const sectionCheckboxes = sectionModal ?
                                        sectionModal.querySelectorAll('input[name="target_sections[]"]') : [];

                                    if (selectAllSections) {
                                        selectAllSections.checked = false;
                                        selectAllSections.onchange = null;
                                        selectAllSections.addEventListener('change', () => {
                                            sectionCheckboxes.forEach(cb => cb.checked = selectAllSections.checked);
                                        });
                                    }

                                    sectionCheckboxes.forEach(cb => {
                                        cb.addEventListener('change', () => {
                                            if (![...sectionCheckboxes].every(cb => cb.checked)) {
                                                if (selectAllSections) selectAllSections.checked = false;
                                            } else {
                                                if (selectAllSections) selectAllSections.checked = true;
                                            }
                                        });
                                    });
                                }

                                deptCheckboxesForSections.forEach(cb => {
                                    cb.addEventListener('change', updateSectionsForOffices);
                                });

                                updateSectionsForOffices();
                                wireSectionSelectAll();
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const dateInput = document.getElementById('eventDate');
                                const startInput = document.getElementById('startTime');
                                const endInput = document.getElementById('endTime');
                                const locationSelect = document.getElementById('eventLocation');
                                const otherInput = document.getElementById('otherLocation');
                                const createBtn = document.querySelector('.btn-create');

                                // Create the warning div
                                const warningDiv = document.createElement('div');
                                warningDiv.classList.add('conflict-warning');
                                warningDiv.style.color = 'red';
                                warningDiv.style.marginTop = '10px';
                                warningDiv.style.fontWeight = '500';
                                warningDiv.style.display = 'none';
                                document.querySelector('.modal form').appendChild(warningDiv);

                                function checkConflict() {
                                    const date = dateInput.value;
                                    const start = startInput.value;
                                    const end = endInput.value;

                                    // Determine the actual location
                                    const location = locationSelect.value === 'Other' ? otherInput.value.trim() : locationSelect.value;

                                    // Skip if required fields are empty
                                    if (!date || !start || !end || !location) {
                                        warningDiv.style.display = 'none';
                                        createBtn.disabled = false;
                                        warningDiv.innerHTML = '';
                                        return;
                                    }

                                    fetch("{{ route('Editor.checkConflict') }}", {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            },
                                            body: JSON.stringify({
                                                date,
                                                start_time: start,
                                                end_time: end,
                                                location
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.conflict) {
                                                createBtn.disabled = true;
                                                warningDiv.style.display = 'block';
                                                warningDiv.innerHTML = `
                                                ⚠️ <b>Schedule Conflict!</b><br>
                                                Event: <b>${data.event.title}</b><br>
                                                Department: ${data.event.department}</br>
                                                Date: ${data.event.date}<br>
                                                Time: ${data.event.start_time} - ${data.event.end_time}<br>
                                                Location: ${data.event.location}
                                            `;
                                            } else {
                                                createBtn.disabled = false;
                                                warningDiv.style.display = 'none';
                                                warningDiv.innerHTML = '';
                                            }
                                        })
                                        .catch(err => console.error('Error checking conflict:', err));
                                }

                                // Listen for changes in all relevant inputs
                                [dateInput, startInput, endInput, locationSelect, otherInput].forEach(el => {
                                    el.addEventListener('change', checkConflict);
                                    el.addEventListener('input', checkConflict); // ensures typing in 'Other' triggers check
                                });
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const selectAllCheckbox = document.getElementById('select_all_create');
                                const yearCheckboxes = document.querySelectorAll('.syear');

                                selectAllCheckbox.addEventListener('change', () => {
                                    yearCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                                });

                                // Optional: if user manually unchecks any year checkbox, uncheck "Select All"
                                yearCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', () => {
                                        if (!cb.checked) {
                                            selectAllCheckbox.checked = false;
                                        } else if ([...yearCheckboxes].every(cb => cb.checked)) {
                                            selectAllCheckbox.checked = true;
                                        }
                                    });
                                });

                                // ------------------------------
                                // Form Submission Validation: Section & Faculty (BOTH required)
                                // ------------------------------
                                const form = document.querySelector('.modal form');
                                const validationErrorDiv = document.getElementById('sectionFacultyValidationError');

                                form.addEventListener('submit', (e) => {
                                    const targetUsers = document.getElementById('targetUsers');

                                    // Only validate if target users is "Students"
                                    if (targetUsers && targetUsers.value === 'Students') {
                                        const sectionCheckboxes = document.querySelectorAll(
                                            'input[name="target_sections[]"]:checked');
                                        const facultyCheckboxes = document.querySelectorAll(
                                            'input[name="target_faculty[]"]:checked');

                                        let errorMessage = '';

                                        // Check if at least one section is selected
                                        if (sectionCheckboxes.length === 0) {
                                            errorMessage += '⚠️ Please select at least one section.<br>';
                                        }

                                        // Check if at least one faculty is selected
                                        if (facultyCheckboxes.length === 0) {
                                            errorMessage += '⚠️ Please select at least one faculty member.';
                                        }

                                        // If any validation fails, prevent submission
                                        if (errorMessage) {
                                            e.preventDefault();
                                            validationErrorDiv.style.display = 'block';
                                            validationErrorDiv.innerHTML = errorMessage;
                                            return false;
                                        }
                                    }

                                    validationErrorDiv.style.display = 'none';
                                });
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const modalOverlay = document.getElementById('attendeesModalOverlay');
                                const modalCloseBtn = document.getElementById('closeAttendeesModal');
                                const tbody = document.getElementById('attendeesTableBody');

                                // OPEN modal
                                document.querySelectorAll('.btn-attendees').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const eventId = this.dataset.eventId;
                                        const dataEl = document.getElementById(`attendees-data-${eventId}`);
                                        const attendees = JSON.parse(dataEl.textContent);

                                        tbody.innerHTML = '';

                                        if (attendees.length === 0) {
                                            tbody.innerHTML = `
                                            <tr>
                                                <td colspan="4" style="text-align:center; padding:15px;">No attendees yet.</td>
                                            </tr>
                                        `;
                                        } else {
                                            attendees.forEach(user => {
                                                tbody.innerHTML += `
                                                <tr>
                                                    <td>${user.name}</td>
                                                    <td>${user.department}</td>
                                                    <td>${user.yearlevel}</td>
                                                    <td>${user.section}</td>
                                                </tr>
                                            `;
                                            });
                                        }

                                        modalOverlay.style.display = 'flex';
                                    });
                                });

                                // CLOSE modal button
                                modalCloseBtn.addEventListener('click', () => {
                                    modalOverlay.style.display = 'none';
                                });

                                // CLOSE modal when clicking outside
                                modalOverlay.addEventListener('click', (e) => {
                                    if (e.target === modalOverlay) {
                                        modalOverlay.style.display = 'none';
                                    }
                                });
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                // ------------------------------
                                // 1. CREATE EVENT CONFIRMATION
                                // ------------------------------
                                const createEventForm = document.querySelector('.modal form'); // Create Event form
                                const createBtn = document.querySelector('.btn-create');

                                if (createEventForm && createBtn) {
                                    createEventForm.addEventListener('submit', function(e) {
                                        const confirmed = confirm(
                                            "Are you sure you want to create this event?  Emails will be sent to the Users");
                                        if (!confirmed) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });
                                }
                            });
                        </script>

                    </div>
                </div>


                <div class="modal-overlay" id="detailsModalOverlay" style="display:none;">
                    <div class="modal"
                        style="width: 600px; max-width: 90%; height: 80%; display: flex; flex-direction: column;">
                        <!-- Header -->
                        <div style="flex-shrink: 0;">
                            <h2>More Details</h2>
                            <p style="margin-bottom: 10px; color: #555;">Provide additional info about the event.</p>
                        </div>

                        <!-- Textarea fills most of modal -->
                        <textarea id="detailsTextarea" placeholder="Type additional details..."
                            style="flex-grow: 1; width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; font-size: 15px; line-height: 1.5; resize: none; outline: none;"></textarea>

                        <!-- Buttons -->
                        <div class="button-group"
                            style="flex-shrink: 0; display: flex; justify-content: flex-end; gap: 10px; margin-top:10px;">
                            <button type="button" class="btn-cancel" id="closeDetailsModalBtn">Cancel</button>
                            <button type="button" class="btn-create" id="saveDetailsBtn">Save Details</button>
                        </div>
                    </div>
                </div>


                <!-- Sample Event Cards -->
                @if ($events->count() > 0)
                    @foreach ($events as $event)
                        <div class="event-card">
                            <div class="event-header">
                                <h2>{{ $event->title }}</h2>
                                <div class="tags">
                                    <span class="tag department">{{ $event->department }}</span>
                                    <span class="tag {{ strtolower($event->computed_status) }}">
                                        {{ ucfirst($event->computed_status) }}
                                    </span>
                                </div>
                            </div>

                            <p>{{ $event->description ?? 'No description available.' }}</p>

                            <div class="event-info">
                                <span>📅 {{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</span>
                                <span>⏰ {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</span>
                                <span>📍 {{ $event->location }}</span>
                                <span>
                                    👤
                                    @if (is_array($event->target_year_levels) && count($event->target_year_levels) > 0)
                                        {{ implode(', ', $event->target_year_levels) }}
                                    @else
                                        {{ $event->target_users }}
                                    @endif
                                </span>
                                @php
                                    $eventSections = $event->target_sections;
                                    if (is_string($eventSections)) {
                                        $eventSections = json_decode($eventSections, true) ?? [];
                                    } elseif (!is_array($eventSections)) {
                                        $eventSections = [];
                                    }

                                    $eventFacultyIds = $event->target_faculty;
                                    if (is_string($eventFacultyIds)) {
                                        $eventFacultyIds = json_decode($eventFacultyIds, true) ?? [];
                                    } elseif (!is_array($eventFacultyIds)) {
                                        $eventFacultyIds = [];
                                    }

                                    $eventFacultyNames = [];
                                    if (!empty($eventFacultyIds)) {
                                        $eventFacultyNames = \App\Models\User::whereIn('id', $eventFacultyIds)
                                            ->pluck('name')
                                            ->toArray();
                                    }
                                @endphp
                                @if (!empty($eventSections))
                                    <span>🏫 {{ implode(', ', $eventSections) }}</span>
                                @endif
                                @if (!empty($eventFacultyNames))
                                    <span>👩‍🏫 {{ implode(', ', $eventFacultyNames) }}</span>
                                @endif


                            </div>

                            <div class="actions">
                                <button class="btn-attendees" data-event-id="{{ $event->id }}">
                                    👥 {{ $event->attendees->count() }} attending
                                </button>

                                <button class="btn-view-details" data-details="{{ $event->more_details }}">
                                    👁️ View Details
                                </button>

                                @if ($event->computed_status !== 'ongoing')
                                    <a href="{{ route('Editor.editEvent', $event->id) }}" class="edit">✏️ Edit</a>

                                    <form action="{{ route('Editor.destroy', $event->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete"
                                            onclick="return confirm('Are you sure you want to delete this event?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="status-locked">🔒 Ongoing</span>
                                @endif
                            </div>
                        </div>
                        @php
                            $attendeesData = $event->attendees->map(function ($user) {
                                // Map department
                                $department =
                                    $user->department === 'OFFICES'
                                        ? $user->office_name ?? 'Office'
                                        : $user->department;

                                // Map year_level safely
                                $year_level =
                                    isset($user->yearlevel) && $user->yearlevel !== ''
                                        ? $user->yearlevel // will be like 1stYear, 2ndYear
                                        : 'N/A';

                                // Map section safely
                                $section = isset($user->section) && $user->section !== '' ? $user->section : 'N/A';

                                return [
                                    'name' => $user->name,
                                    'department' => $department,
                                    'yearlevel' => $year_level,
                                    'section' => $section,
                                ];
                            });
                        @endphp

                        <script type="application/json" id="attendees-data-{{ $event->id }}">
                            {!! json_encode($attendeesData) !!}
                        </script>
                    @endforeach
                @else
                    <p style="color:#555; text-align:center;">No events found. Create one to get started!</p>
                @endif
                <!-- View Details Modal -->
                <div class="modal-overlay" id="viewDetailsModal" style="display:none;">
                    <div class="modal"
                        style="width: 600px; max-width: 90%; height: 80%; display: flex; flex-direction: column;">
                        <div style="flex-shrink: 0;">
                            <h2>Event Details</h2>
                        </div>

                        <div id="detailsContent"
                            style="flex-grow: 1; overflow-y: auto; padding: 12px; border: 1px solid #ccc; border-radius: 10px; white-space: pre-wrap;">
                            <!-- Content will be injected by JS -->
                        </div>

                        <div class="button-group"
                            style="flex-shrink: 0; display: flex; justify-content: flex-end; margin-top:10px;">
                            <button type="button" class="btn-cancel" id="closeViewDetailsBtn">Close</button>
                        </div>
                    </div>
                </div>
                <!-- ATTENDEES MODAL -->
                <div id="attendeesModalOverlay" class="attendees-modal-overlay">
                    <div class="attendees-modal">
                        <div class="attendees-modal-header">
                            <h2>Event Attendees</h2>
                            <button id="closeAttendeesModal" class="close-btn">&times;</button>
                        </div>
                        <div class="attendees-modal-body">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Year Level</th>
                                        <th>Section</th>
                                    </tr>
                                </thead>
                                <tbody id="attendeesTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @if (session('success'))
            <div id="toast" class="toast show">
                <p>{{ session('success') }}</p>
            </div>
        @endif
    </body>

    </html>
</x-editorLayout>
