<x-editorLayout>
    <div class="dashboard_container">
        <!-- Welcome -->
        <section class="dashboard_welcome_card">
            <div>
                <h2>Welcome back, {{ Auth::user()->name }}!</h2>
                <p>
                    @if (Auth::user()->department === 'OFFICES')
                        {{ Auth::user()->office_name }}
                    @else
                        {{ Auth::user()->title }}
                    @endif Dashboard
                </p>
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

        <!-- Stats -->
        {{-- <section class="dashboard_stats">
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>Total Events</h3>
                <p>{{ $myEventsCount }}</p>
            </div>
        </section> --}}

        <!-- Upcoming Events -->
        <section class="dashboard_upcoming_card">
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
            <p>For the next 30 days</p>

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
                                👤
                                @if (is_array($event->target_year_levels) && count($event->target_year_levels) > 0)
                                    @foreach ($event->target_year_levels as $yearLevel)
                                        {{ $yearLevel . ',' }}
                                    @endforeach
                                @else
                                    {{ $event->target_users }}
                                @endif
                            </div>

                            <div class="dashboard_event_details">
                                {{ $event->description ?? 'No description provided.' }}</div>
                            <div class="dashboard_event_details">SY.{{ $event->school_year }}</div>

                            <div class="dashboard_event_details">
                                👥 {{ $event->attendees()->count() }} attending
                            </div>

                            <div class="dashboard_event_tags">
                                <span class="dashboard_tag dashboard_tag_admin">
                                    @if ($event->department === 'OFFICES')
                                        {{ $event->user->office_name ?? 'Office' }}
                                    @else
                                        {{ $event->department }}
                                    @endif
                                </span>

                                <span
                                    class="dashboard_tag 
                                    @if ($event->computed_status === 'ongoing') dashboard_tag_ongoing
                                    @elseif($event->computed_status === 'upcoming') dashboard_tag_upcoming
                                    @elseif($event->computed_status === 'cancelled') dashboard_tag_cancelled
                                    @elseif($event->computed_status === 'completed') dashboard_tag_completed @endif
                                ">
                                    {{ ucfirst($event->computed_status) }}
                                </span>
                            </div>

                            <button class="dashboard_view_btn" data-id="{{ $event->id }}"
                                data-details="{{ e($event->more_details ?? 'No additional details.') }}"
                                style="padding:10px 22px; background:#e8ecf5; border:none; border-radius:10px; font-size:1rem; cursor:pointer; font-weight:600; color:#36415d; margin-top:10px;">
                                👁️ View Details
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('eventSearch');
            const eventContainer = document.querySelector('.dashboard_upcoming_card');
            const clearButton = document.querySelector('.dashboard_clear_btn');

            // --- Search as you type ---
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                fetch(`/editor/dashboard/search?query=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const events = data.events;

                        // Reset header to match the Blade file's header
                        eventContainer.innerHTML = `
                        <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
                    `;

                        if (events.length === 0) {
                            eventContainer.innerHTML += `<p>No events found.</p>`;
                            return;
                        }

                        // Render matching events
                        events.forEach(event => {
                            // --- THIS IS THE UPDATED/FIXED PART ---

                            const date = new Date(event.date).toLocaleDateString('en-US');

                            // FIX 1: Use pre-formatted times from the controller
                            const startTime = event.formatted_start_time || '';
                            const endTime = event.formatted_end_time || '';

                            eventContainer.innerHTML += `
                            <div class="dashboard_event_card">
                                <div class="dashboard_event_title">${event.title}</div>
                                <div class="dashboard_event_details">
                                    📅 ${date} &nbsp; 🕓 ${startTime} - ${endTime} &nbsp; 📍 ${event.location || ''}
                                </div>
                                <div class="dashboard_event_details">${event.description || ''}</div>
                                <div class="dashboard_event_tags">
                                    <span class="dashboard_tag dashboard_tag_admin">${event.tag_name}</span>
                                    <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                                </div>
                            </div>
                        `;
                            // --- END OF UPDATED/FIXED PART ---
                        });
                    })
                    .catch(error => console.error('Error fetching search results:', error));
            });

            // --- Clear button resets search ---
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            });
        });
    </script>
    <script>
        // -------------------------------
        // VIEW DETAILS MODAL
        // -------------------------------
        const detailsModal = document.getElementById("dashboardDetailsModalOverlay");
        const detailsTextarea = document.getElementById("dashboardDetailsTextarea");
        const detailsCloseBtn = document.getElementById("dashboardDetailsCloseBtn");

        // When clicking a View Details button
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("dashboard_view_btn")) {
                const details = e.target.dataset.details || "No additional details.";
                detailsTextarea.value = details.trim();
                detailsModal.style.display = "flex";
            }
        });

        // Close button
        detailsCloseBtn.addEventListener("click", () => {
            detailsModal.style.display = "none";
        });

        // Click outside close
        window.addEventListener("click", (e) => {
            if (e.target === detailsModal) {
                detailsModal.style.display = "none";
            }
        });
    </script>

    <!-- MORE DETAILS MODAL -->
    <div id="dashboardDetailsModalOverlay"
        style="
        display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.65); z-index:9999;
        justify-content:center; align-items:center;
        padding:20px;
    ">

        <div
            style="
            background:#ffffff;
            padding:30px;
            width:85%;
            max-width:1200px;
            height:85%;
            border-radius:18px;
            box-shadow:0 12px 45px rgba(0,0,0,0.30);
            display:flex;
            flex-direction:column;
            animation: fadeInScale 0.28s ease-out;

            border-top:7px solid #2b5eff;
        ">

            <h2
                style="
            margin-bottom:15px;
            font-size:1.7rem;
            color:#2b3a67;
            font-weight:700;
        ">
                Event Details</h2>

            <textarea id="dashboardDetailsTextarea" readonly
                style="
            width:100%;
            flex:1;
            resize:none;
            padding:15px;
            font-size:1.05rem;
            border-radius:12px;
            border:1px solid #d0d7e3;
            outline:none;
            transition:0.2s;
            background:#f9fbff;
        "
                onfocus="this.style.border='1px solid #2b5eff'; this.style.boxShadow='0 0 0 3px rgba(43,94,255,0.15)'"
                onblur="this.style.border='1px solid #d0d7e3'; this.style.boxShadow='none'"></textarea>

            <div style="display:flex; justify-content:flex-end; margin-top:18px;">
                <button id="dashboardDetailsCloseBtn"
                    style="
                padding:10px 22px;
                background:#e8ecf5;
                border:none;
                border-radius:10px;
                font-size:1rem;
                cursor:pointer;
                transition:0.25s;
                font-weight:600;
                color:#36415d;
            "
                    onmouseover="this.style.background='#d4d9e6'" onmouseout="this.style.background='#e8ecf5'">
                    Close
                </button>
            </div>

        </div>
    </div>

</x-editorLayout>
