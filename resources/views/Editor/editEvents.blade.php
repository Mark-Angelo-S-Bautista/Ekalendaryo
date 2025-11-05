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
                <label>Date</label>
                <input type="date" name="date" value="{{ $event->date }}" required>
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
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                        <div class="checkbox-inline">
                            <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                {{ in_array($year, $event->target_year_levels ?? []) ? 'checked' : '' }}>
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
</x-editorLayout>
