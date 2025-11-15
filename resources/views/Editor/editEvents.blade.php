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

            <div class="form-group">
                <label>Event title</label>
                <input type="text" name="title" value="{{ $event->title }}" required>
            </div>

            <div class="form-group">
                <label>Event description</label>
                <textarea name="description">{{ $event->description }}</textarea>
            </div>

            <div class="form-group">
                <label>More Details</label>

                <!-- Button to open the modal -->
                <button type="button" id="openMoreDetailsBtn" class="btn-update" style="margin-bottom:10px;">
                    Edit More Details
                </button>

                <!-- Hidden input that will store the final saved text -->
                <input type="hidden" id="moreDetailsInput" name="more_details"
                    value="{{ old('more_details', $event->more_details) }}">
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $event->date }}" required min="{{ date('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" value="{{ $event->start_time }}" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" value="{{ $event->end_time }}" required>
            </div>

            <!-- Updated Event Location Field -->
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

            <div class="form-group">
                <label>Target Year Levels</label>
                <div class="checkbox-group">

                    {{-- NEW: Safe Guard Logic --}}
                    @php
                        // 1. Get the current value, defaulting to an empty string if null.
                        $currentLevels = $event->target_year_levels;

                        // 2. Determine the array to use for checking:
                        if (is_array($currentLevels)) {
                            $selectedLevels = $currentLevels;
                        } elseif (is_string($currentLevels)) {
                            // This handles old string/JSON data if casting failed.
                            // We decode the JSON string, defaulting to an empty array if decoding fails.
                            $selectedLevels = json_decode($currentLevels, true) ?? [];
                        } else {
                            // Default to an empty array for any other unexpected format (like null).
                            $selectedLevels = [];
                        }
                    @endphp

                    {{-- Loop now uses the guaranteed array: $selectedLevels --}}
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                        <div class="checkbox-inline">
                            <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                {{ in_array($year, $selectedLevels) ? 'checked' : '' }}>
                            {{ $year }}
                        </div>
                    @endforeach
                </div>
            </div>

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

            // OPEN modal
            openMoreBtn.addEventListener("click", () => {
                textarea.value = hiddenInput.value || "";
                modal.style.display = "flex";
            });

            // CLOSE modal
            closeMoreBtn.addEventListener("click", () => {
                modal.style.display = "none";
            });

            // SAVE details and close
            saveMoreBtn.addEventListener("click", () => {
                hiddenInput.value = textarea.value;
                modal.style.display = "none";
            });
        });
    </script>
    <!-- ==========================
        MORE DETAILS MODAL
    ============================= -->
    <div id="moreDetailsModalOverlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">

        <div
            style="background:#fff;
                padding:25px;
                width:80%;
                max-width:1200px;
                height:85%;                 /* <-- made modal taller */
                border-radius:15px;
                box-shadow:0 10px 30px rgba(0,0,0,0.25);
                display:flex;
                flex-direction:column;
                animation: fadeInScale 0.25s ease-out;">

            <h2>Edit More Details</h2>

            <textarea id="moreDetailsTextarea"
                style="width:100%;
                   flex:1;                    /* <-- textarea fills most of modal */
                   resize:vertical;
                   font-size:1rem;
                   padding:10px;
                   margin-top:10px;
                   margin-bottom:15px;"></textarea>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" id="closeMoreDetailsBtn" class="btn-cancel">Cancel</button>
                <button type="button" id="saveMoreDetailsBtn" class="btn-update">Save</button>
            </div>
        </div>
    </div>
</x-editorLayout>
