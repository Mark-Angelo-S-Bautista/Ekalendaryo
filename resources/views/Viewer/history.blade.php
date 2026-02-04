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
                <form id="feedbackForm" method="POST" action="{{ route('Viewer.feedback.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" id="feedbackEventId">

                    <div style="margin-bottom: 12px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Overall Rating</label>
                        <div <div style="display:flex; gap:8px; align-items:center; font-size:2rem;">
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="rating" id="rating-{{ $i }}"
                                    value="{{ $i }}" style="display:none;">
                                <label for="rating-{{ $i }}"
                                    title="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                                    style="cursor:pointer; color: #e0e0e0;">★</label>
                            @endfor
                        </div>
                        <small style="color:#6b7280;">Select 1–5 stars</small>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px;">General Questions</label>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">How satisfied are you with the event?
                            </div>
                            <label style="margin-right:10px;"><input type="radio" name="q_satisfaction"
                                    value="Very Satisfied"> Very Satisfied</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_satisfaction"
                                    value="Satisfied"> Satisfied</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_satisfaction"
                                    value="Neutral"> Neutral</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_satisfaction"
                                    value="Dissatisfied"> Dissatisfied</label>
                        </div>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">Was the event well organized?</div>
                            <label style="margin-right:10px;"><input type="radio" name="q_organization"
                                    value="Yes"> Yes</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_organization"
                                    value="Somewhat"> Somewhat</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_organization"
                                    value="No"> No</label>
                        </div>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">Was the event helpfull to you?</div>
                            <label style="margin-right:10px;"><input type="radio" name="q_relevance" value="Yes">
                                Yes</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_relevance" value="Somewhat">
                                Somewhat</label>
                            <label style="margin-right:10px;"><input type="radio" name="q_relevance" value="No">
                                No</label>
                        </div>
                    </div>

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
