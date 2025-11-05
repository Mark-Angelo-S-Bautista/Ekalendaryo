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
                                <input type="date" id="eventDate" name="date" required>
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
                                <label>Event location</label>
                                <input type="text" id="eventLocation" name="location" placeholder="Event location"
                                    required>
                            </div>

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
                                    <span class="tag department">department</span>
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
                            </div>

                            <div class="actions">
                                <a href="{{ route('Editor.editEvent', $event->id) }}" class="edit">✏️</a>

                                <form action="{{ route('Editor.destroy', $event->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete"
                                        onclick="return confirm('Are you sure you want to delete this event?')">🗑️</button>
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
