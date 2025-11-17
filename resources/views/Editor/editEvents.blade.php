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
            @endif

            {{-- Target Year Levels --}}
            @if (auth()->user()->title === 'Offices' || auth()->user()->title === 'Department Head')
                @php
                    $levels = $event->target_year_levels;
                    if (is_string($levels)) {
                        $levels = json_decode($levels, true) ?? [];
                    }
                @endphp

                <div class="form-group" id="targetYearLevelsContainer"
                    style="{{ $event->target_users !== 'Students' ? 'display:none;' : '' }}">
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

            <div class="button-group">
                <button type="submit" class="btn-update">Update Event</button>
                <a href="{{ route('Editor.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
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

        // Show/hide Year Levels based on Target Users
        const targetUsersSelect = document.getElementById('targetUsers');
        const yearLevelsContainer = document.getElementById('targetYearLevelsContainer');
        if (targetUsersSelect && yearLevelsContainer) {
            targetUsersSelect.addEventListener('change', function() {
                yearLevelsContainer.style.display = this.value === 'Students' ? 'block' : 'none';
            });
        }

        // Select All Departments logic
        const selectAllCheckbox = document.getElementById('selectAllDepartments');
        const deptCheckboxes = document.querySelectorAll('.dept-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                deptCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            deptCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else if ([...deptCheckboxes].every(c => c.checked)) {
                        selectAllCheckbox.checked = true;
                    }
                });
            });

            // Initialize select all if all are checked
            if ([...deptCheckboxes].every(c => c.checked)) selectAllCheckbox.checked = true;
        }
    </script>

    {{-- JS SCRIPT FOR EVENTS DETAILS MODAL --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const openMoreBtn = document.getElementById("openMoreDetailsBtn");
            const modal = document.getElementById("moreDetailsModalOverlay");
            const closeMoreBtn = document.getElementById("closeMoreDetailsBtn");
            const saveMoreBtn = document.getElementById("saveMoreDetailsBtn");
            const textarea = document.getElementById("moreDetailsTextarea");
            const hiddenInput = document.getElementById("moreDetailsInput");

            openMoreBtn.addEventListener("click", () => {
                textarea.value = hiddenInput.value || "";
                modal.style.display = "flex";
            });

            closeMoreBtn.addEventListener("click", () => modal.style.display = "none");
            saveMoreBtn.addEventListener("click", () => {
                hiddenInput.value = textarea.value;
                modal.style.display = "none";
            });
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

            if (targetUsers) {
                targetUsers.addEventListener("change", function() {
                    yearLevels.style.display = (this.value === "Students") ? "block" : "none";
                });
            }

            // Select all year levels
            const selectAll = document.getElementById("select_all_edit");
            const yearChecks = document.querySelectorAll(".syear");

            if (selectAll) {
                selectAll.addEventListener("change", function() {
                    yearChecks.forEach(cb => cb.checked = this.checked);
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
