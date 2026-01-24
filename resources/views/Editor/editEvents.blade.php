<x-editorLayout>

    <head>
        @vite(['resources/css/editor/editEvents.css'])
    </head>

    <div class="edit-event-container">
        <h1>Edit Event</h1>

        @if (session('conflict'))
            <div class="conflict-message">
                {{ session('conflict') }}
            </div>
        @endif

        @if (session('conflict_event'))
            @php $conflictEvent = session('conflict_event'); @endphp
            <div class="conflict-details">
                <h4>Conflicting Event Schedule Detected:</h4>
                <p><strong>Title:</strong> {{ $conflictEvent['title'] }}</p>
                <p><strong>Department:</strong> {{ $conflictEvent['department'] }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($conflictEvent['date'])->format('F d, Y') }}</p>
                <p><strong>Time:</strong>
                    {{ \Carbon\Carbon::parse($conflictEvent['start_time'])->format('h:i A') }} -
                    {{ \Carbon\Carbon::parse($conflictEvent['end_time'])->format('h:i A') }}
                </p>
                <p><strong>Location:</strong> {{ $conflictEvent['location'] }}</p>
            </div>
        @endif

        <form action="{{ route('Editor.update', $event->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Event Title --}}
            <div class="form-group">
                <label>Event title</label>
                <input type="text" id="eventTitle" name="title" value="{{ old('title', $event->title) }}"
                    placeholder="Event title">
            </div>

            {{-- Event Description --}}
            <div class="form-group">
                <label>Event description</label>
                <input type="text" id="eventDescription" name="description"
                    value="{{ old('description', $event->description) }}" placeholder="Event Description">
            </div>

            {{-- More Details --}}
            <div class="form-group">
                <label>More Details</label>
                <button type="button" id="openMoreDetailsBtn" class="btn-update" style="margin-bottom:10px;">
                    Edit More Details
                </button>
                <input type="hidden" id="moreDetailsInput" name="more_details"
                    value="{{ old('more_details', $event->more_details) }}">
            </div>


            <input type="hidden" id="moreDetailsInput" name="more_details"
                value="{{ old('more_details', $event->more_details) }}">

            {{-- Date --}}
            <div class="form-group">
                <label>Date</label>
                <input type="date" id="eventDate" name="date" value="{{ old('date', $event->date) }}"
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
            </div>

            {{-- Start Time --}}
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" id="startTime" name="start_time"
                    value="{{ old('start_time', $event->start_time) }}" min="07:00" max="17:00">
            </div>

            {{-- End Time --}}
            <div class="form-group">
                <label>End Time</label>
                <input type="time" id="endTime" name="end_time" value="{{ old('end_time', $event->end_time) }}"
                    min="07:00" max="17:00">
            </div>

            {{-- Location --}}
            <div class="form-group">
                <label for="eventLocation">Event Location</label>

                @php
                    $presetLocations = [
                        'Covered Court',
                        'Activity Center',
                        'Library',
                        'Audio Visual Room',
                        'Auditorium',
                    ];
                    $isOther = !in_array($event->location, $presetLocations);
                @endphp

                <select id="eventLocation" name="location" onchange="toggleOtherLocation()" class="form-control">
                    <option value="">-- Select a location --</option>

                    @foreach ($presetLocations as $loc)
                        <option value="{{ $loc }}" {{ $event->location === $loc ? 'selected' : '' }}>
                            {{ $loc }}
                        </option>
                    @endforeach

                    <option value="Other" {{ $isOther ? 'selected' : '' }}>Other</option>
                </select>

                {{-- If user used "Other", show input --}}
                <input type="text" id="otherLocation" name="other_location" placeholder="Please specify location"
                    class="form-control" style="{{ $isOther ? '' : 'display:none;' }} margin-top:10px;"
                    value="{{ $isOther ? $event->location : '' }}">
            </div>

            {{-- TARGET DEPARTMENTS AND USERS — SAME LOGIC AS CREATE FORM --}}
            @if (auth()->user()->title === 'Offices' || auth()->user()->title === 'Department Head')
                <div class="form-group">
                    <label for="targetDepartment">Target Department</label>

                    @php
                        $user = Auth::user();
                        $userTitle = $user->title;
                        $userDepartment = $user->department_name ?? $user->department;
                        $selectedDepartments = $event->target_department ?? [];
                        if (is_string($selectedDepartments)) {
                            $selectedDepartments = json_decode($selectedDepartments, true) ?? [];
                        }
                    @endphp

                    {{-- Department Head sees only their department (fixed) --}}
                    @if ($userTitle === 'Department Head')
                        <input type="hidden" name="target_department[]" value="{{ $userDepartment }}">
                        <p style="color:#6c757d; margin-top:5px;">
                            Targeting is set to your department:
                            <strong>{{ $userDepartment }}</strong> (Fixed)
                        </p>

                        {{-- Offices sees all except OFFICES --}}
                    @else
                        <div class="checkbox-grid">
                            @foreach ($departments as $dept)
                                @if ($dept->department_name !== 'OFFICES')
                                    <label class="checkbox-item">
                                        <input type="checkbox" class="dept-checkbox" name="target_department[]"
                                            value="{{ $dept->department_name }}"
                                            {{ in_array($dept->department_name, $selectedDepartments) ? 'checked' : '' }}>
                                        <span>{{ $dept->department_name }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Target Users --}}
                <div class="form-group">
                    <label for="targetUsers">Target Users</label>
                    <select id="targetUsers" name="target_users" class="form-control">
                        <option value="">-- Select Users --</option>
                        <option value="Faculty" {{ $event->target_users === 'Faculty' ? 'selected' : '' }}>Faculty
                        </option>

                        @if ($userTitle === 'Offices')
                            <option value="Department Heads"
                                {{ $event->target_users === 'Department Heads' ? 'selected' : '' }}>
                                Department Heads
                            </option>
                        @endif

                        <option value="Students" {{ $event->target_users === 'Students' ? 'selected' : '' }}>Students
                        </option>
                    </select>
                </div>
            @else
                {{-- For non-Offices/non-Department Head users, show Target Users only --}}
                <div class="form-group">
                    <label for="targetUsers">Target Users</label>
                    <select id="targetUsers" name="target_users" class="form-control">
                        <option value="">-- Select Users --</option>
                        <option value="Faculty" {{ $event->target_users === 'Faculty' ? 'selected' : '' }}>Faculty
                        </option>
                        <option value="Students" {{ $event->target_users === 'Students' ? 'selected' : '' }}>Students
                        </option>
                    </select>
                </div>
            @endif

            {{-- Target Year Levels --}}
            @if (auth()->user()->title === 'Offices' || auth()->user()->title === 'Department Head')
                @php
                    $levels = $event->target_year_levels;
                    if (is_string($levels)) {
                        $levels = json_decode($levels, true) ?? [];
                    }
                @endphp

                <div class="form-group" id="targetYearLevelsContainer" style="display:none;">
                    <label>Target Year Levels</label>
                    <p class="note">Select which year levels of students will receive notifications</p>

                    <div class="checkbox_select">
                        <div class="checkbox-inline">
                            <input type="checkbox" id="select_all_edit">
                            <label for="select_all_edit">Select All Year Levels</label>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                            <div class="checkbox-inline">
                                <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                    class="syear" {{ in_array($year, $levels) ? 'checked' : '' }}>
                                {{ $year }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Section Button (only shown when target_users is Students) --}}
            @php
                $selectedSections = $event->target_sections ?? [];
                if (is_string($selectedSections)) {
                    $selectedSections = json_decode($selectedSections, true) ?? [];
                }
            @endphp

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
                                <input type="checkbox" name="target_sections[]" value="{{ $section }}"
                                    {{ in_array($section, $selectedSections) ? 'checked' : '' }}>
                                <span>{{ $section }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Faculty Button (only shown when target_users is Students) --}}
            @php
                $selectedFaculty = $event->target_faculty ?? [];
                if (is_string($selectedFaculty)) {
                    $selectedFaculty = json_decode($selectedFaculty, true) ?? [];
                }
            @endphp

            <!-- Faculty Modal -->
            <div id="facultyModalOverlay" class="custom-modal-overlay">
                <div class="custom-modal">
                    <h3>Select Faculty</h3>
                    <div class="checkbox-grid">
                        @foreach ($faculty as $f)
                            <label class="checkbox-item">
                                <input type="checkbox" name="target_faculty[]" value="{{ $f->id }}"
                                    {{ in_array($f->id, $selectedFaculty) ? 'checked' : '' }}>
                                <span>{{ $f->name }} ({{ $f->department }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-update">Update Event</button>
                <a href="{{ route('Editor.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    {{-- JS SCRIPT FOR EVENTS DETAILS MODAL AND MODALS --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const openMoreBtn = document.getElementById("openMoreDetailsBtn");
            const modal = document.getElementById("moreDetailsModalOverlay");
            const closeMoreBtn = document.getElementById("closeMoreDetailsBtn");
            const saveMoreBtn = document.getElementById("saveMoreDetailsBtn");
            const textarea = document.getElementById("moreDetailsTextarea");
            const hiddenInput = document.getElementById("moreDetailsInput");

            if (openMoreBtn) {
                openMoreBtn.addEventListener("click", () => {
                    textarea.value = hiddenInput.value || "";
                    modal.style.display = "flex";
                });
            }

            if (closeMoreBtn) {
                closeMoreBtn.addEventListener("click", () => modal.style.display = "none");
            }

            if (saveMoreBtn) {
                saveMoreBtn.addEventListener("click", () => {
                    hiddenInput.value = textarea.value;
                    modal.style.display = "none";
                });
            }
        });
    </script>

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

        document.addEventListener("DOMContentLoaded", () => {
            const targetUsers = document.getElementById("targetUsers");
            const yearLevels = document.getElementById("targetYearLevelsContainer");
            const openSectionBtn = document.getElementById("openSectionModalBtn");
            const openFacultyBtn = document.getElementById("openFacultyModalBtn");
            const sectionModalOverlay = document.getElementById("sectionModalOverlay");
            const facultyModalOverlay = document.getElementById("facultyModalOverlay");

            function toggleButtons() {
                const isStudents = targetUsers && targetUsers.value === 'Students';
                if (isStudents) {
                    if (yearLevels) yearLevels.style.display = 'block';
                    if (openSectionBtn) openSectionBtn.style.display = 'inline-block';
                    if (openFacultyBtn) openFacultyBtn.style.display = 'inline-block';
                    if (sectionModalOverlay) sectionModalOverlay.style.display = 'flex';
                    if (facultyModalOverlay) facultyModalOverlay.style.display = 'flex';
                } else {
                    if (yearLevels) yearLevels.style.display = 'none';
                    if (openSectionBtn) openSectionBtn.style.display = 'none';
                    if (openFacultyBtn) openFacultyBtn.style.display = 'none';
                    if (sectionModalOverlay) sectionModalOverlay.style.display = 'none';
                    if (facultyModalOverlay) facultyModalOverlay.style.display = 'none';
                }
            }

            if (targetUsers) {
                targetUsers.addEventListener("change", function() {
                    const isStudents = this.value === "Students";
                    if (yearLevels) yearLevels.style.display = isStudents ? "block" : "none";
                    toggleButtons();
                });
                toggleButtons(); // initial check on page load
            } else {
                // If targetUsers doesn't exist, hide the buttons and modals by default
                if (openSectionBtn) openSectionBtn.style.display = 'none';
                if (openFacultyBtn) openFacultyBtn.style.display = 'none';
                if (sectionModalOverlay) sectionModalOverlay.style.display = 'none';
                if (facultyModalOverlay) facultyModalOverlay.style.display = 'none';
            }

            // Select all year levels
            const selectAll = document.getElementById("select_all_edit");
            const yearChecks = document.querySelectorAll(".syear");

            if (selectAll) {
                selectAll.addEventListener("change", function() {
                    yearChecks.forEach(cb => cb.checked = this.checked);
                });
            }

            // ==============================
            // Faculty Modal Logic
            // ==============================
            const facultyModal = document.getElementById('facultyModalOverlay');
            const closeFacultyBtn = document.getElementById('closeFacultyModalBtn');

            if (openFacultyBtn) {
                openFacultyBtn.addEventListener('click', () => {
                    facultyModal.classList.add('active');
                });
            }

            if (closeFacultyBtn) {
                closeFacultyBtn.addEventListener('click', () => {
                    facultyModal.classList.remove('active');
                });
            }

            // ==============================
            // Section Modal Logic
            // ==============================
            const sectionModal = document.getElementById('sectionModalOverlay');
            const closeSectionBtn = document.getElementById('closeSectionModalBtn');

            if (openSectionBtn) {
                openSectionBtn.addEventListener('click', () => {
                    sectionModal.classList.add('active');
                });
            }

            if (closeSectionBtn) {
                closeSectionBtn.addEventListener('click', () => {
                    sectionModal.classList.remove('active');
                });
            }

            // ==============================
            // Section Modal "Select All" Logic
            // ==============================
            const selectAllSections = document.getElementById('selectAllSections');
            const sectionCheckboxes = sectionModal ? sectionModal.querySelectorAll(
                'input[name="target_sections[]"]') : [];

            if (selectAllSections) {
                selectAllSections.addEventListener('change', () => {
                    sectionCheckboxes.forEach(cb => cb.checked = selectAllSections.checked);
                });

                sectionCheckboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        if (![...sectionCheckboxes].every(cb => cb.checked)) {
                            selectAllSections.checked = false;
                        } else {
                            selectAllSections.checked = true;
                        }
                    });
                });
            }
        });
    </script>

    <!-- MORE DETAILS MODAL -->
    <div id="moreDetailsModalOverlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
        <div
            style="background:#fff; padding:25px; width:80%; max-width:1200px; height:85%;
                    border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.25);
                    display:flex; flex-direction:column; animation: fadeInScale 0.25s ease-out;">
            <h2>Edit More Details</h2>
            <textarea id="moreDetailsTextarea"
                style="width:100%; flex:1; resize:vertical; font-size:1rem; padding:10px; margin-top:10px; margin-bottom:15px;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" id="closeMoreDetailsBtn" class="btn-cancel">Cancel</button>
                <button type="button" id="saveMoreDetailsBtn" class="btn-update">Save</button>
            </div>
        </div>
    </div>
</x-editorLayout>
