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
                            <span class="tag">{{ $event->department }}</span>
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
                            <span>🕒 {{ $event->school_year }}</span>
                            <span>👥 {{ $event->attendees()->count() }} attending</span>

                            {{-- Feedback Button --}}
                            <span>
                                💬
                                <button class="feedback-btn" data-event-id="{{ $event->id }}">
                                    {{ $event->feedbacks_count }} feedback
                                </button>
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
                <h3>Event Feedback</h3>
                <div id="feedbackList">
                    <!-- Feedback items will be loaded here via AJAX -->
                </div>
            </div>
        </div>

    </body>

    </html>
</x-editorLayout>
