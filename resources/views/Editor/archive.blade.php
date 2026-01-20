<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>eKalendaryo Archive</title>
        @vite(['resources/css/editor/archive.css', 'resources/js/editor/archive.js'])
    </head>

    <body>

        <main>
            <h2>Archive</h2>
            <p>Past school year events and records</p>

            <div class="card">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span>▾</span>
                    <div>
                        <strong>SY. {{ $currentSchoolYear }}</strong>
                        <p style="margin: 0; font-size: 14px; color: gray;">
                            {{ $totalEvents }} events
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="subcard" onclick="openModal('eventsModal')">
                        <h4>📅 Events Archive</h4>
                        <p>{{ $totalEvents }} completed events from SY.{{ $currentSchoolYear }}</p>
                    </div>

                    <div class="subcard" onclick="openModal('deletedModal')">
                        <h4>🗑️ Recently Cancelled</h4>
                        <p>View and restore deleted items</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Recently Deleted Modal -->
        <div id="deletedModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('deletedModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">🗑️</span>
                    Recently Cancelled - SY.{{ $currentSchoolYear }}
                </div>
                <p>{{ $deletedEvents->count() }} deleted events</p>

                <div id="deletedList" class="eventlist_">
                    @forelse ($deletedEvents as $event)
                        <div class="event">
                            <h4>{{ $event->title }}</h4>
                            <p>{{ $event->description ?? 'No description provided.' }}</p>
                            <p>📍 {{ $event->location }} | ⏰
                                {{ \Carbon\Carbon::parse($event->date)->format('F j, Y') }}
                                ({{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }})
                            </p>
                            <p>👤 Year Level: {{ $event->target_year_levels_str }} | 🧍‍♂️
                                {{ $event->attendees->count() ?? 0 }}
                                attendees
                            </p>
                        </div>
                        <div class="pagination">
                            {{ $deletedEvents->links('vendor.pagination.simple') }}
                        </div>
                    @empty
                        <p>No deleted events for this school year.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Optional: Events Archive Modal -->
        <div id="eventsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('eventsModal')">&times;</span>
                <div class="modal-header">
                    <span class="icon">📅</span>
                    Events Archive - SY.{{ $currentSchoolYear }}
                </div>
                <p>{{ $totalEvents }} events</p>
                <!-- You can populate archived events here if needed -->
            </div>
        </div>
    </body>

    </html>
</x-editorLayout>
