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

                        <form action="{{ route('Editor.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>Event title</label>
                                <input type="text" id="eventTitle" name="title" placeholder="Event title" required>
                            </div>

                            <div class="form-group">
                                <label>Event description</label>
                                <input type="text" id="eventDescription" name="description"
                                    placeholder="Event Description">
                            </div>

                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" id="eventDate" name="date" required min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" id="startTime" name="start_time" required>
                            </div>

                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" id="endTime" name="end_time" required>
                            </div>

                            <div class="form-group">
                                <label for="eventLocation">Event Location</label>
                                <select id="eventLocation" name="location" required onchange="toggleOtherLocation()"
                                    class="form-control">
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
                                    style="display: none; margin-top: 10px;">
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

                            <div class="form-group">
                                <label>Target Year Levels</label>
                                <p class="note">Select which year levels of students will receive notifications for
                                    this
                                    event</p>

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

                            <div class="button-group">
                                <button type="button" class="btn-cancel" id="closeModalBtn">Cancel</button>
                                <button type="submit" class="btn-create">Create Event</button>
                            </div>
                        </form>
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
                            });
                        </script>
                    </div>
                </div>


                <!-- Sample Event Cards -->
                @if ($events->count() > 0)
                    @foreach ($events as $event)
                        <div class="event-card">
                            <div class="event-header">
                                <h2>{{ $event->title }}</h2>
                                {{-- DITO AY DAPAT MAGING DYNAMIC YUNG TAGS BASED DUN SA ACCOUNT --}}
                                <div class="tags">
                                    <span class="tag department">{{ $event->department }}</span>
                                    <span class="tag upcoming">
                                        @if (strtotime($event->date) > time())
                                            upcoming
                                        @else
                                            past
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <p>{{ $event->description ?? 'No description available.' }}</p>

                            <div class="event-info">
                                <span>📅 {{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</span>
                                <span>⏰ {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}</span>
                                <span>📍 {{ $event->location }}</span>
                                <span>
                                    👥
                                    @if (is_array($event->target_year_levels) && count($event->target_year_levels) > 0)
                                        @foreach ($event->target_year_levels as $yearLevel)
                                            {{ $yearLevel . ',' }}
                                        @endforeach
                                    @else
                                        <p>No specific year levels targeted for this event.</p>
                                    @endif
                                </span>
                            </div>

                            <div class="actions">
                                <a href="{{ route('Editor.editEvent', $event->id) }}" class="edit">✏️Edit</a>

                                <form action="{{ route('Editor.destroy', $event->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete"
                                        onclick="return confirm('Are you sure you want to delete this event?')">🗑️Delete</button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                @else
                    <p style="color:#555; text-align:center;">No events found. Create one to get started!</p>
                @endif

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
