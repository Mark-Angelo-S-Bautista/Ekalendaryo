<x-viewerLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>eKalendaryo - Event History</title>
        @vite(['resources/css/viewer/history.css', 'resources/js/viewer/history.js'])
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

                            {{-- Feedback Button --}}
                            <span>
                                <button
                                    class="feedback-btn 
                                    {{ in_array($event->id, $submittedFeedbackIds) ? 'submitted' : '' }}"
                                    data-event-id="{{ $event->id }}" data-event-title="{{ $event->title }}"
                                    @if (in_array($event->id, $submittedFeedbackIds)) disabled style="background-color: #22c55e;" @endif>
                                    {{ in_array($event->id, $submittedFeedbackIds) ? '✔️ Feedback Submitted' : $event->feedback_count ?? '💬 Feedback' }}
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

                <h3 id="feedbackEventTitle">Event Feedback</h3>

                {{-- Feedback Form --}}
                <form id="feedbackForm" method="POST" action="{{ url('/viewer/events/feedback') }}">
                    @csrf
                    <input type="hidden" name="event_id" id="feedbackEventId">

                    <div style="margin-bottom: 12px;">
                        <textarea name="comment" id="comment" rows="20" placeholder="Write your feedback here..." required></textarea>
                        <div id="charCounter" style="font-size: 0.9rem; color: gray;">
                            0 / 1000 characters
                        </div>
                        <div id="charError" style="color: red; display: none; font-size: 0.9rem;">
                            Maximum 1000 characters reached!
                        </div>
                    </div>

                    <button type="submit" id="feedbackSubmitBtn" class="submit-feedback-btn">
                        Submit Feedback
                    </button>
                </form>
            </div>
        </div>

        {{-- Toast Notif --}}
        <div id="toast" class="toast"
            style="position: fixed; bottom: 20px; right: 20px; background-color: #22c55e; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: none; z-index: 9999; font-weight: bold;">
            Feedback submitted successfully!
        </div>

    </body>

    </html>
</x-viewerLayout>
