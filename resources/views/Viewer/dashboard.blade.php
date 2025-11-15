<x-viewerLayout>
    <div class="dashboard_container">
        <!-- Welcome -->
        <section class="dashboard_welcome_card">
            <div>
                <h2>Welcome back, {{ Auth::user()->name }}!</h2>
                <p>{{ $title }} Dashboard</p>
                <p class="dashboard_school_year">Current School Year: SY.2025-2026</p>
            </div>
        </section>

        <!-- Search -->
        <section class="dashboard_search_card">
            <div class="dashboard_search_box">
                <input type="text" id="eventSearch" placeholder="Search events..." class="dashboard_search_input">
                <button class="dashboard_clear_btn">Clear</button>
            </div>
        </section>

        <!-- Upcoming Events -->
        <section class="dashboard_upcoming_card">
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>

            @forelse ($events as $event)
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
                            <p>No specific year levels targeted for this event.</p>
                        @endif
                    </div>
                    <div class="dashboard_event_details">{{ $event->description ?? 'No description provided.' }}</div>
                    <div class="dashboard_event_details">{{ $event->school_year }}</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">
                            @if ($event->department === 'OFFICES')
                                {{ $event->user->office_name ?? 'OFFICES' }}
                            @else
                                {{ $event->department }}
                            @endif
                        </span>
                        <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                        <span><!-- View Details Button -->
                            <button class="dashboard_view_btn"
                                data-details="{{ e($event->more_details ?? 'No additional details.') }}"
                                style="
                                    padding: 6px 15px;
                                    background: #2b5eff;
                                    color: #fff;
                                    border: none;
                                    border-radius: 8px;
                                    font-size: 0.78rem;
                                    cursor: pointer;
                                    font-weight: 500;
                                    transition: 0.25s;
                                "
                                onmouseover="this.style.background='#1a3ed8'"
                                onmouseout="this.style.background='#2b5eff'">
                                👁️ View Details
                            </button></span>
                    </div>
                </div>
            @empty
                <p>No upcoming events found.</p>
            @endforelse
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
