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
                <input type="text" name="title" value="{{ $event->title }}" required>
            </div>

            {{-- Event Description --}}
            <div class="form-group">
                <label>Event description</label>
                <textarea name="description">{{ $event->description }}</textarea>
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

            {{-- Date --}}
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $event->date }}" required min="{{ date('Y-m-d') }}">
            </div>

            {{-- Start Time --}}
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" value="{{ $event->start_time }}" required>
            </div>

            {{-- End Time --}}
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" value="{{ $event->end_time }}" required>
            </div>

            {{-- Event Location --}}
            <div class="form-group">
                <label for="eventLocation">Event Location</label>
                @php
                    $locations = ['Covered Court', 'Activity Center', 'Library', 'Audio Visual Room', 'Auditorium'];
                    $isOther = !in_array($event->location, $locations);
                @endphp
                <select id="eventLocation" name="location" required onchange="toggleOtherLocation()"
                    class="form-control">
                    <option value="">-- Select a location --</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}" {{ $event->location === $location ? 'selected' : '' }}>
                            {{ $location }}
                        </option>
                    @endforeach
                    <option value="Other" {{ $isOther ? 'selected' : '' }}>Other</option>
                </select>
                <input type="text" id="otherLocation" name="other_location" placeholder="Please specify location"
                    class="form-control" style="{{ $isOther ? '' : 'display: none;' }} margin-top: 10px;"
                    value="{{ $isOther ? $event->location : '' }}">
            </div>

            {{-- Target Departments --}}
            @if (auth()->user()->department === 'OFFICES')
                <div class="form-group">
                    <label for="targetDepartment">Target Department</label>
                    <div class="checkbox-grid">
                        {{-- "All Departments" --}}
                        <label class="checkbox-item">
                            <input type="checkbox" id="selectAllDepartments">
                            <span>All Departments</span>
                        </label>

                        {{-- Dynamic Departments --}}
                        @foreach ($departments as $dept)
                            <label class="checkbox-item">
                                <input type="checkbox" class="dept-checkbox" name="target_department[]"
                                    value="{{ $dept->department_name }}"
                                    {{ is_array($event->target_department) && in_array($dept->department_name, $event->target_department) ? 'checked' : '' }}>
                                <span>{{ $dept->department_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Target Users --}}
                <div class="form-group">
                    <label for="targetUsers">Target Users</label>
                    <select id="targetUsers" name="target_users" class="form-control">
                        <option value="">-- Select Users --</option>
                        <option value="Faculty" {{ $event->target_users === 'Faculty' ? 'selected' : '' }}>Faculty
                        </option>
                        <option value="Department Heads"
                            {{ $event->target_users === 'Department Heads' ? 'selected' : '' }}>Department Heads
                        </option>
                        <option value="Students" {{ $event->target_users === 'Students' ? 'selected' : '' }}>Students
                        </option>
                    </select>
                </div>

                {{-- Target Year Levels (only visible if Students is selected) --}}
                <div class="form-group" id="targetYearLevelsContainer"
                    style="{{ $event->target_users !== 'Students' ? 'display:none;' : '' }}">
                    <label>Target Year Levels</label>
                    <p class="note">Select which year levels of students will receive notifications for this event</p>
                    <div class="checkbox-group">
                        @php
                            $currentLevels = $event->target_year_levels;
                            if (is_array($currentLevels)) {
                                $selectedLevels = $currentLevels;
                            } elseif (is_string($currentLevels)) {
                                $selectedLevels = json_decode($currentLevels, true) ?? [];
                            } else {
                                $selectedLevels = [];
                            }
                        @endphp

                        @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                            <div class="checkbox-inline">
                                <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                    {{ in_array($year, $selectedLevels) ? 'checked' : '' }}>
                                {{ $year }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (auth()->user()->department !== 'OFFICES')
                {{-- Target Year Levels (only show if event has year levels set) --}}
                <div class="form-group" id="targetYearLevelsContainer"
                    style="{{ empty($event->target_year_levels) ? 'display:none;' : '' }}">
                    <label>Target Year Levels</label>
                    <p class="note">Select which year levels of students will receive notifications for this event</p>
                    <div class="checkbox-group">
                        @php
                            $currentLevels = $event->target_year_levels;
                            if (is_array($currentLevels)) {
                                $selectedLevels = $currentLevels;
                            } elseif (is_string($currentLevels)) {
                                $selectedLevels = json_decode($currentLevels, true) ?? [];
                            } else {
                                $selectedLevels = [];
                            }
                        @endphp

                        @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                            <div class="checkbox-inline">
                                <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                    {{ in_array($year, $selectedLevels) ? 'checked' : '' }}>
                                {{ $year }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="submit" class="btn-update">Update Event</button>
            <a href="{{ route('Editor.index') }}" class="btn-cancel">Cancel</a>
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
