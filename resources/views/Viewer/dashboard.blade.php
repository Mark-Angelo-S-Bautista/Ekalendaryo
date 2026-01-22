    <x-viewerLayout>

        <head>
            <meta name="csrf-token" content="{{ csrf_token() }}">
        </head>
        <div class="dashboard_container">
            <!-- Welcome -->
            <section class="dashboard_welcome_card">
                <div>
                    <h2>Welcome back, {{ Auth::user()->name }}!</h2>
                    <p>{{ $title }} Dashboard</p>
                    <p class="dashboard_school_year">Current School Year: {{ $currentSchoolYearName }}</p>
                </div>
            </section>

            {{-- <!-- Search -->
            <section class="dashboard_search_card">
                <div class="dashboard_search_box">
                    <input type="text" id="eventSearch" placeholder="Search events..." class="dashboard_search_input">
                    <button class="dashboard_clear_btn">Clear</button>
                </div>
            </section> --}}

            <!-- Upcoming Events -->
            <section class="dashboard_upcoming_card">
                <h3 class="dashboard_upcoming_title">Upcoming Events</h3>

                @if ($events->isEmpty())
                    <p>No upcoming events found.</p>
                @else
                    <div class="dashboard_events_grid">
                        @foreach ($events as $event)
                            <div class="dashboard_event_card">
                                <div class="dashboard_event_title">{{ $event->title }}</div>
                                <div class="dashboard_event_details">
                                    📅 {{ \Carbon\Carbon::parse($event->date)->format('n/j/Y') }}
                                    &nbsp;&nbsp; 🕓 {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                                    - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    &nbsp;&nbsp; 📍 {{ $event->location }}
                                </div>
                                <div class="dashboard_event_details">
                                    👥
                                    @if (is_array($event->target_year_levels) && count($event->target_year_levels) > 0)
                                        @foreach ($event->target_year_levels as $yearLevel)
                                            {{ $yearLevel . ',' }}
                                        @endforeach
                                    @else
                                        {{ $event->target_users }}
                                    @endif
                                </div>
                                <div class="dashboard_event_details">
                                    {{ $event->description ?? 'No description provided.' }}
                                </div>
                                <div class="dashboard_event_details">{{ $event->school_year }}</div>
                                <div class="dashboard_event_tags">
                                    <span class="dashboard_tag dashboard_tag_admin">
                                        @if ($event->department === 'OFFICES')
                                            {{ $event->user->office_name ?? 'OFFICES' }}
                                        @else
                                            {{ $event->department }}
                                        @endif
                                    </span>
                                    <span
                                        class="dashboard_tag 
                                                @if ($event->computed_status === 'upcoming') dashboard_tag_upcoming
                                                @elseif($event->computed_status === 'ongoing') dashboard_tag_ongoing
                                                @elseif($event->computed_status === 'cancelled') dashboard_tag_cancelled
                                                @elseif($event->computed_status === 'completed') dashboard_tag_completed @endif
                                            ">
                                        {{ ucfirst($event->computed_status) }}
                                    </span>
                                </div>
                                <div>
                                    <button class="dashboard_view_btn"
                                        data-details="{{ e($event->more_details ?? 'No additional details.') }}"
                                        style="margin-right: 110px; padding:10px 22px; background:#e8ecf5; border:none; border-radius:10px; font-size:1rem; cursor:pointer; font-weight:600; color:#36415d; margin-top:10px;">
                                        👁️ View Details
                                    </button>
                                    <button class="dashboard_attend_btn" data-event-id="{{ $event->id }}"
                                        style="padding:10px 22px; 
                                            border-radius:10px; 
                                            font-size:1rem; 
                                            cursor:pointer; 
                                            font-weight:600; 
                                            margin-top:10px; 
                                            margin-left:5px; 
                                            transition: all 0.3s ease;
                                            @if ($event->attendees->contains(Auth::id())) background:#4CAF50; 
                                                color:#ffffff; 
                                                border:1px solid #4CAF50; 
                                                cursor:not-allowed;
                                            @else
                                                background:#ffffff; 
                                                color:#36415d; 
                                                border:1px solid #36415d; @endif
                                        "
                                        @if ($event->attendees->contains(Auth::id())) disabled @endif>
                                        @if ($event->attendees->contains(Auth::id()))
                                            ✅ Attending
                                        @else
                                            ✋ Attend
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <!-- VIEW DETAILS MODAL -->
        <div id="viewerDetailsModalOverlay" class="viewer-modal-overlay">
            <div class="viewer-modal-content">
                <h2>Event Details</h2>
                <div id="viewerDetailsContent" class="viewer-modal-text"></div>
                <div style="display:flex; justify-content:flex-end; margin-top:18px;">
                    <button id="viewerDetailsCloseBtn" class="viewer-modal-close-btn">Close</button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.dashboard_attend_btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const btn = this;
                        const eventId = btn.dataset.eventId;

                        fetch(`/viewer/dashboard/${eventId}/attend`, { // <-- Corrected URL
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'already' || data.status === 'success') {
                                    btn.innerHTML = "✅ Attending";
                                    btn.style.backgroundColor = "#4CAF50"; // green
                                    btn.style.color = "#ffffff";
                                    btn.style.border = "1px solid #4CAF50";
                                    btn.style.transition = "all 0.3s ease";
                                    btn.disabled = true;
                                    btn.style.cursor = "not-allowed";

                                    // Update attendee count
                                    const countDisplay = btn.closest('.dashboard_event_card')
                                        .querySelector('.attendees_count');
                                    if (countDisplay) {
                                        countDisplay.innerText =
                                            `${data.attendees_count} attending`;
                                    }
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                btn.disabled = false;
                                btn.style.cursor = 'pointer';
                            });
                    });
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('viewerDetailsModalOverlay');
                const modalContent = document.getElementById('viewerDetailsContent');
                const closeBtn = document.getElementById('viewerDetailsCloseBtn');

                // Open modal
                document.querySelectorAll('.dashboard_view_btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        modalContent.innerHTML = (btn.dataset.details || 'No additional details.')
                            .replace(/\n/g, '<br>');
                        modal.style.display = 'flex';
                    });
                });

                // Close modal  
                closeBtn.addEventListener('click', () => modal.style.display = 'none');

                // Close modal when clicking outside
                modal.addEventListener('click', e => {
                    if (e.target === modal) modal.style.display = 'none';
                });
            });
        </script>
    </x-viewerLayout>
