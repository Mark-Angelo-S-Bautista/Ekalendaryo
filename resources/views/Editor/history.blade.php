<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>eKalendaryo - Event History</title>
        @vite(['resources/css/editor/history.css', 'resources/js/editor/history.js'])
    </head>

    <body>
        <div class="container">
            <h2>Event History</h2>

            <div class="filter-bar">
                <input type="text" id="search" placeholder="🔍 Search events...">
                <button id="clearSearch">Clear</button>
            </div>

            <div id="eventList">
                @forelse($events as $event)
                    <div class="event-card" data-type="{{ $event->department }}">
                        <div class="event-header">
                            <h3>{{ $event->title }}</h3>
                            <span class="status completed">completed</span>
                        </div>

                        <p class="event-details">
                            {{ $event->description ?? 'No description provided.' }}
                        </p>

                        <div class="event-meta">
                            <span>📅 {{ \Carbon\Carbon::parse($event->date)->format('m/d/Y') }}</span>
                            <span>⏰ {{ $event->start_time }} - {{ $event->end_time }}</span>
                            <span>📍 {{ $event->location }}</span>
                            <span>👤 {{ $event->department }}</span>
                            <span>🕒 SY.{{ $event->school_year }}</span>
                            <span>👥 {{ $event->attendees()->count() }} attending</span>
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

                            {{-- Feedback Button --}}
                            <span>
                                💬
                                <button class="feedback-btn" data-event-id="{{ $event->id }}">
                                    {{ $event->feedbacks_count }} feedback
                                </button>

                                📄
                                @if ($event->report_path)
                                    <a href="{{ route('Editor.downloadReport', $event->id) }}" class="report-btn">
                                        Download Report
                                    </a>

                                    {{-- Remove Report Button --}}
                                    <button class="remove-report-btn" data-event-id="{{ $event->id }}"
                                        style="margin-left:10px;">
                                        Delete Report
                                    </button>
                                @else
                                    <button class="upload-report-btn" data-event-id="{{ $event->id }}">
                                        Upload Report
                                    </button>
                                @endif
                            </span>
                        </div>
                    </div>
                @empty
                    <p>No completed events yet.</p>
                @endforelse
            </div>

            <div class="footer-box">
                <span>📊</span>
                <span>
                    Showing {{ $events->firstItem() }} – {{ $events->lastItem() }}
                    of {{ $events->total() }} events
                </span>
            </div>

            {{ $events->links('vendor.pagination.simple') }}
        </div>

        {{-- Feedback Modal --}}
        <div id="feedbackModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>💬 Event Feedback</h3>
                <div id="feedbackList">
                    <!-- Feedback items will be loaded here via AJAX -->
                </div>
            </div>
        </div>

        {{-- Report Upload Modal --}}
        <div id="reportModal" class="modal">
            <div class="modal-content report-modal">
                <span class="close-btn" id="reportCloseBtn">&times;</span>
                <h3>📄 Manage Event Report</h3>
                <form id="reportForm" method="POST" enctype="multipart/form-data" class="report-form">
                    @csrf
                    <label for="reportInput" class="custom-file-upload">
                        Choose PDF File
                    </label>
                    <input type="file" name="report" id="reportInput" accept=".pdf">
                    <p id="selectedFile" class="selected-file">No file selected</p>
                    <input type="hidden" name="event_id" id="reportEventId">
                    <button type="submit" class="upload-btn">Upload PDF</button>
                </form>
            </div>
        </div>

    </body>

    </html>
</x-editorLayout>
