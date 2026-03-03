<x-usermanLayout>
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
                    <div class="column-title">📝 My Published Events</div>

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
                                    <span>
                                        👥 <button class="btn-attendees" data-event-id="{{ $event->id }}"
                                            data-attendees="{{ json_encode($event->attendees) }}"
                                            style="background: none; border: none; color: inherit; cursor: pointer; text-decoration: underline; padding: 0; font: inherit;">
                                            {{ $event->attendees()->count() }} attending
                                        </button>
                                    </span>
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

                                        <button class="feedback-btn" data-event-id="{{ $event->id }}">
                                            {{ $event->feedbacks_count }} 💬 feedback
                                        </button>


                                        @if ($event->report_path)
                                            <a href="{{ route('UserManagement.downloadReport', $event->id) }}"
                                                class="report-btn">
                                                📄 Download Report
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
                            <p>No completed events yet.</p>
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

        {{-- FEEDBACK MODAL (View feedback for events you created) --}}
        <div id="feedbackModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Event Feedback</h2>
                    <button id="closeFeedbackModal" class="close-btn">&times;</button>
                </div>
                <div class="modal-body" id="feedbackModalContent">
                    <p>Loading...</p>
                </div>
            </div>
        </div>

        {{-- SUBMIT FEEDBACK MODAL (for events invited to) --}}
        <div id="submitFeedbackModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Submit Feedback for <span id="submitFeedbackEventTitle"></span></h2>
                    <button id="submitFeedbackCloseBtn" class="close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="submitFeedbackForm" action="{{ route('UserManagement.feedback.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="event_id" id="submitFeedbackEventId">

                        <div class="form-group">
                            <label>Rating</label>
                            <div class="star-rating">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="rating-{{ $i }}" name="rating"
                                        value="{{ $i }}" required>
                                    <label for="rating-{{ $i }}">&#9733;</label>
                                @endfor
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="submitComment">Comment (optional)</label>
                            <textarea id="submitComment" name="comment" rows="4" maxlength="1000"
                                placeholder="Share your thoughts about this event..."></textarea>
                            <div class="char-counter" id="submitCharCounter">0 / 1000 characters</div>
                            <div class="char-error" id="submitCharError" style="display:none; color:red;">Max 1000
                                characters reached!</div>
                        </div>

                        <button type="submit" class="submit-btn">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- UPLOAD REPORT MODAL -->
        <div id="uploadReportModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Event Report</h2>
                    <button id="closeUploadModal" class="close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="uploadReportForm" action="{{ route('UserManagement.uploadReport') }}" method="POST"
                        enctype="multipart/form-data">
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
        </div>

        <!-- ATTENDEES MODAL -->
        <div id="attendeesModalOverlay" class="attendees-modal-overlay">
            <div class="attendees-modal">
                <div class="attendees-modal-header">
                    <h2>Event Attendees</h2>
                    <button id="closeAttendeesModal" class="close-btn">&times;</button>
                </div>
                <div class="attendees-modal-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Year Level</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody id="attendeesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div id="toast" class="toast"
            style="position: fixed; bottom: 20px; right: 20px; background-color: #22c55e; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: none; z-index: 9999; font-weight: bold;">
            Feedback submitted successfully!
        </div>

        <script>
            // Attendees Modal functionality
            document.addEventListener('DOMContentLoaded', function() {
                const modalOverlay = document.getElementById('attendeesModalOverlay');
                const modalCloseBtn = document.getElementById('closeAttendeesModal');
                const tbody = document.getElementById('attendeesTableBody');

                // OPEN modal
                document.querySelectorAll('.btn-attendees').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const eventId = this.dataset.eventId;
                        const attendeesData = JSON.parse(this.dataset.attendees || '[]');

                        // Clear previous content
                        tbody.innerHTML = '';

                        if (attendeesData.length === 0) {
                            tbody.innerHTML =
                                '<tr><td colspan="4" style="text-align:center;">No attendees</td></tr>';
                        } else {
                            attendeesData.forEach(att => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>
                                        <div style="font-size: 0.85em; color: #6b7280; font-weight: 500;"><strong>${att.title || ''}</strong></div>
                                        <div>${att.name || 'N/A'}</div>
                                    </td>
                                    <td>${att.department || 'N/A'}</td>
                                    <td>${att.yearlevel || 'N/A'}</td>
                                    <td>${att.section || 'N/A'}</td>
                                `;
                                tbody.appendChild(row);
                            });
                        }

                        modalOverlay.style.display = 'flex';
                    });
                });

                // CLOSE modal button
                modalCloseBtn.addEventListener('click', () => {
                    modalOverlay.style.display = 'none';
                });

                // CLOSE modal when clicking outside
                modalOverlay.addEventListener('click', (e) => {
                    if (e.target === modalOverlay) {
                        modalOverlay.style.display = 'none';
                    }
                });
            });

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
</x-usermanLayout>
