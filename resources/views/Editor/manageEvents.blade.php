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

                                    <div class="checkbox-group">
                                        <div class="checkbox-inline">
                                            <input type="checkbox" name="target_year_levels[]" value="1st Year"
                                                class="syear"> 1st Year
                                        </div>
                                        <div class="checkbox-inline">
                                            <input type="checkbox" name="target_year_levels[]" value="2nd Year"
                                                class="syear"> 2nd Year
                                        </div>
                                        <div class="checkbox-inline">
                                            <input type="checkbox" name="target_year_levels[]" value="3rd Year"
                                                class="syear"> 3rd Year
                                        </div>
                                        <div class="checkbox-inline">
                                            <input type="checkbox" name="target_year_levels[]" value="4th Year"
                                                class="syear"> 4th Year
                                        </div>
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
                                        <div class="checkbox-grid">
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
                            document.addEventListener('DOMContentLoaded', () => {
                                // ------------------------------
                                // Year Levels Select All Logic
                                // ------------------------------
                                const selectAllCheckbox = document.getElementById('select_all_create');
                                const yearCheckboxes = document.querySelectorAll('.syear');

                                selectAllCheckbox.addEventListener('change', () => {
                                    yearCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                                });

                                yearCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', () => {
                                        if (![...yearCheckboxes].every(cb => cb.checked)) {
                                            selectAllCheckbox.checked = false;
                                        } else {
                                            selectAllCheckbox.checked = true;
                                        }
                                    });
                                });

                                // ------------------------------
                                // Target Users logic for Section & Faculty buttons
                                // ------------------------------
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

                                // ------------------------------
                                // Faculty Modal Logic
                                // ------------------------------
                                const facultyModal = document.getElementById('facultyModalOverlay');
                                const closeFacultyBtn = document.getElementById('closeFacultyModalBtn');

                                openFacultyBtn.addEventListener('click', () => {
                                    facultyModal.classList.add('active');
                                });
                                closeFacultyBtn.addEventListener('click', () => {
                                    facultyModal.classList.remove('active');
                                });

                                // ------------------------------
                                // Section Modal Logic
                                // ------------------------------
                                const sectionModal = document.getElementById('sectionModalOverlay');
                                const closeSectionBtn = document.getElementById('closeSectionModalBtn');

                                openSectionBtn.addEventListener('click', () => {
                                    sectionModal.classList.add('active');
                                });
                                closeSectionBtn.addEventListener('click', () => {
                                    sectionModal.classList.remove('active');
                                });

                                // ------------------------------
                                // Section Modal "Select All" Logic
                                // ------------------------------
                                const selectAllSections = document.getElementById('selectAllSections');
                                const sectionCheckboxes = sectionModal.querySelectorAll('input[name="target_sections[]"]');

                                selectAllSections.addEventListener('change', () => {
                                    sectionCheckboxes.forEach(cb => cb.checked = selectAllSections.checked);
                                });

                                // Uncheck Select All if any individual section is unchecked
                                sectionCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', () => {
                                        if (![...sectionCheckboxes].every(cb => cb.checked)) {
                                            selectAllSections.checked = false;
                                        } else {
                                            selectAllSections.checked = true;
                                        }
                                    });
                                });
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


                            </div>

                            <div class="actions">
                                <span>👥 {{ $event->attendees()->count() }} attending</span>

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
