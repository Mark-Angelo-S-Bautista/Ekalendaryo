<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>eKalendaryo - Event History</title>
        @vite(['resources/css/editor/history.css', 'resources/js/editor/history.js'])

    </head>

    <body>
        <div class="container">
            <h2>Event History</h2>

            <div class="history-columns">
                {{-- COLUMN 1: Events Created by User (Report Upload) --}}
                <div class="column-container">
                    <div class="column-title">📝 My Created Events</div>

                    <div id="createdEventList">
                        @forelse($createdEvents as $event)
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
                                    <span>SY.{{ $event->school_year }}</span>
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

                                    <span>
                                        💬
                                        <button class="feedback-btn" data-event-id="{{ $event->id }}">
                                            {{ $event->feedbacks_count }} feedback
                                        </button>

                                        📄
                                        @if ($event->report_path)
                                            <a href="{{ route('Editor.downloadReport', $event->id) }}"
                                                class="report-btn">
                                                Download Report
                                            </a>

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
                            <p>No created events yet.</p>
                        @endforelse
                    </div>

                    @if ($createdEvents->hasPages())
                        <div class="footer-box">
                            <span>📊</span>
                            <span>
                                Showing {{ $createdEvents->firstItem() }} – {{ $createdEvents->lastItem() }}
                                of {{ $createdEvents->total() }} events
                            </span>
                        </div>
                        {{ $createdEvents->links('vendor.pagination.simple') }}
                    @endif
                </div>

                {{-- COLUMN 2: Events User Was Invited To (Feedback Submission) --}}
                <div class="column-container">
                    <div class="column-title">📬 Events Invited To</div>

                    <div id="invitedEventList">
                        @forelse($invitedEvents as $event)
                            <div class="event-card" data-type="{{ $event->department }}">
                                <div class="event-header">
                                    <h3>{{ $event->title }}</h3>
                                    @if ($event->department === 'OFFICES')
                                        <span class="tag"
                                            style="background-color: #22c55e;">{{ $event->user->office_name ?? 'Office' }}</span>
                                    @else
                                        <span class="tag"
                                            style="background-color: #22c55e;">{{ $event->department }}</span>
                                    @endif
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
                                    <span>SY {{ $event->school_year }}</span>
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
                                        @php
                                            $hasAttended = in_array($event->id, $attendedEventIds);
                                            $hasSubmittedFeedback = in_array($event->id, $submittedFeedbackIds);
                                        @endphp

                                        <button
                                            class="submit-feedback-btn {{ $hasSubmittedFeedback ? 'submitted' : '' }}"
                                            data-event-id="{{ $event->id }}" data-event-title="{{ $event->title }}"
                                            @if (!$hasAttended || $hasSubmittedFeedback) disabled 
                                                style="background-color: {{ $hasSubmittedFeedback ? '#22c55e' : '#cccccc' }}; cursor: not-allowed;" @endif>
                                            @if ($hasSubmittedFeedback)
                                                ✔️ Feedback Submitted
                                            @elseif (!$hasAttended)
                                                🔒 Feedback
                                            @else
                                                💬 Feedback
                                            @endif
                                        </button>
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p>No invited events yet.</p>
                        @endforelse
                    </div>

                    @if ($invitedEvents->hasPages())
                        <div class="footer-box">
                            <span>📊</span>
                            <span>
                                Showing {{ $invitedEvents->firstItem() }} – {{ $invitedEvents->lastItem() }}
                                of {{ $invitedEvents->total() }} events
                            </span>
                        </div>
                        {{ $invitedEvents->links('vendor.pagination.simple') }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Feedback View Modal (for created events) --}}
        <div id="feedbackModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>💬 Event Feedback</h3>
                <div id="feedbackList">
                    <!-- Feedback items will be loaded here via AJAX -->
                </div>
            </div>
        </div>

        {{-- Feedback Submission Modal (for invited events) --}}
        <div id="submitFeedbackModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" id="submitFeedbackCloseBtn">&times;</span>

                <h3 id="submitFeedbackEventTitle">Event Feedback</h3>

                {{-- Feedback Form --}}
                <form id="submitFeedbackForm" method="POST" action="{{ route('Editor.feedback.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" id="submitFeedbackEventId">

                    <div>
                        <label>Overall Rating</label>
                        <div style="display:flex; gap:8px; align-items:center; font-size:2rem;">
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="rating" id="rating-{{ $i }}"
                                    value="{{ $i }}" style="display:none;">
                                <label for="rating-{{ $i }}"
                                    title="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                                    style="cursor:pointer; color: #e0e0e0; font-weight: normal;">★</label>
                            @endfor
                        </div>
                        <small style="color:#6b7280;">Select 1–5 stars</small>
                    </div>

                    <div>
                        <label>General Questions</label>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">How satisfied are you with the event?
                            </div>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_satisfaction" value="Very Satisfied"> Very Satisfied</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_satisfaction" value="Satisfied"> Satisfied</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_satisfaction" value="Neutral"> Neutral</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_satisfaction" value="Dissatisfied"> Dissatisfied</label>
                        </div>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">Was the event well organized?</div>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_organization" value="Yes"> Yes</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_organization" value="Somewhat"> Somewhat</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_organization" value="No"> No</label>
                        </div>

                        <div style="margin-bottom:8px;">
                            <div style="font-size:0.95rem; margin-bottom:4px;">Was the event helpfull to you?</div>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_relevance" value="Yes">
                                Yes</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_relevance" value="Somewhat">
                                Somewhat</label>
                            <label style="margin-right:10px; font-weight: normal;"><input type="radio"
                                    name="q_relevance" value="No">
                                No</label>
                        </div>
                    </div>

                    <div>
                        <label for="submitComment">Your Feedback</label>
                        <textarea name="comment" id="submitComment" rows="20" placeholder="Write your feedback here..." required></textarea>
                        <div id="submitCharCounter" style="font-size: 0.9rem; color: gray;">
                            0 / 1000 characters
                        </div>
                        <div id="submitCharError" style="color: red; display: none; font-size: 0.9rem;">
                            Maximum 1000 characters reached!
                        </div>
                    </div>

                    <button type="submit" id="submitFeedbackBtn">
                        Submit Feedback
                    </button>
                </form>
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

        {{-- Toast Notification --}}
        <div id="toast" class="toast"
            style="position: fixed; bottom: 20px; right: 20px; background-color: #22c55e; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: none; z-index: 9999; font-weight: bold;">
            Feedback submitted successfully!
        </div>

        <script>
            // Star rating functionality
            document.querySelectorAll('input[name="rating"]').forEach((radio, index) => {
                const label = document.querySelector(`label[for="rating-${index + 1}"]`);

                label.addEventListener('click', function() {
                    // Update all stars up to selected one
                    document.querySelectorAll('label[for^="rating-"]').forEach((lbl, i) => {
                        if (i <= index) {
                            lbl.style.color = '#fbbf24'; // gold color
                        } else {
                            lbl.style.color = '#e0e0e0'; // gray
                        }
                    });
                });
            });

            // Feedback submission modal for invited events
            document.querySelectorAll('.submit-feedback-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.disabled) return;

                    const eventId = this.dataset.eventId;
                    const eventTitle = this.dataset.eventTitle;

                    document.getElementById('submitFeedbackEventId').value = eventId;
                    document.getElementById('submitFeedbackEventTitle').textContent = eventTitle;
                    document.getElementById('submitFeedbackModal').style.display = 'flex';
                });
            });

            // Close submit feedback modal
            document.getElementById('submitFeedbackCloseBtn').addEventListener('click', () => {
                document.getElementById('submitFeedbackModal').style.display = 'none';
            });

            // Character counter for feedback
            const submitCommentArea = document.getElementById('submitComment');
            const submitCharCounter = document.getElementById('submitCharCounter');
            const submitCharError = document.getElementById('submitCharError');

            submitCommentArea.addEventListener('input', function() {
                const length = this.value.length;
                submitCharCounter.textContent = `${length} / 1000 characters`;

                if (length > 1000) {
                    submitCharError.style.display = 'block';
                    this.value = this.value.substring(0, 1000);
                } else {
                    submitCharError.style.display = 'none';
                }
            });

            // Submit feedback form
            document.getElementById('submitFeedbackForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('submitFeedbackModal').style.display = 'none';

                            // Show toast
                            const toast = document.getElementById('toast');
                            toast.style.display = 'block';
                            setTimeout(() => {
                                toast.style.display = 'none';
                            }, 3000);

                            // Reload page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(data.message || 'Error submitting feedback');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('An error occurred while submitting feedback');
                    });
            });

            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target.id === 'submitFeedbackModal') {
                    document.getElementById('submitFeedbackModal').style.display = 'none';
                }
            });
        </script>

    </body>

    </html>
</x-editorLayout>
