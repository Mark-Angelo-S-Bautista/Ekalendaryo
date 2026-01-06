<x-usermanLayout>
    <div class="dashboard_container">
        <section class="dashboard_welcome_card">
            <div>
                <h2>Welcome back, {{ Auth::user()->name ?? 'User' }}!</h2>
                <p>
                    @if (Auth::user()->department === 'OFFICES')
                        {{ Auth::user()->office_name }}
                    @else
                        {{ Auth::user()->title }}
                    @endif Dashboard
                </p>
                <p class="dashboard_school_year">Current School Year: SY.2025-2026</p>
            </div>
            <form action="{{ url('/usermanagement/dashboard') }}" method="POST">
                @csrf
                <button type="submit" class="dashboard_change_year_btn">
                    Change School Year
                </button>
            </form>
        </section>

        <section class="dashboard_search_card">
            <div class="dashboard_search_box">
                <input type="text" id="eventSearch" placeholder="Search events..." class="dashboard_search_input">
                <button class="dashboard_clear_btn">Clear</button>
            </div>
        </section>

        <section class="dashboard_stats">
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>Total Departments and Offices Events</h3>
                <p>{{ $departmentCounts->sum() }}</p>
            </div>
        </section>

        <section class="dashboard_upcoming_card">
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
            <p>For the next 30 days</p>

            <div id="eventsWrapper">
                @if ($upcomingEvents->isEmpty())
                    <p>No upcoming events.</p>
                @else
                    <div class="dashboard_events_grid">
                        @foreach ($upcomingEvents as $event)
                            <div class="dashboard_event_card">
                                <div class="dashboard_event_title">{{ $event->title }}</div>
                                <div class="dashboard_event_details">
                                    📅 {{ \Carbon\Carbon::parse($event->date)->format('n/j/Y') }}
                                    &nbsp;&nbsp; 🕓 {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    &nbsp;&nbsp; 📍 {{ $event->location }}
                                </div>
                                <div class="dashboard_event_details">
                                    👥
                                    @php
                                        if (!empty($event->target_year_levels)) {
                                            $yearLevelsText = implode(', ', $event->target_year_levels);
                                        } else {
                                            $yearLevelsText = $event->target_users ?? 'No target group';
                                        }
                                    @endphp
                                    {{ $yearLevelsText }}
                                </div>
                                <div class="dashboard_event_details">
                                    {{ $event->description ?? 'No description provided.' }}</div>
                                <div class="dashboard_event_details">{{ $event->school_year }}</div>
                                <div class="dashboard_event_tags">
                                    <span class="dashboard_tag dashboard_tag_admin">
                                        @if ($event->department === 'OFFICES')
                                            {{ $event->office_name }}
                                        @else
                                            {{ $event->department }}
                                        @endif
                                    </span>
                                    <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                                </div>

                                <button class="dashboard_view_btn" data-details='@json($event->more_details ?? 'No additional details.')'>
                                    👁️ View Details
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="dashboard_modal" id="dashboard_department_modal">
        <div class="dashboard_modal_content">
            <div class="dashboard_modal_header">
                <h3>📁 Department Events</h3>
                <button class="dashboard_close_btn" id="dashboard_close_modal">&times;</button>
            </div>
            <div class="dashboard_modal_body">
                <p>Event counts by department</p>
                <div class="dashboard_department_grid">
                    @foreach ($departmentCounts as $dept => $count)
                        <div class="dashboard_department_card">
                            <h4>{{ $dept }}</h4>
                            <p>{{ $count }} events</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="userDetailsModalOverlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:9999; justify-content:center; align-items:center; padding:20px;">
        <div
            style="background:#ffffff; padding:30px; width:85%; max-width:1000px; height:75%; border-radius:18px; box-shadow:0 12px 45px rgba(0,0,0,0.30); display:flex; flex-direction:column; border-top:6px solid #2b5eff;">
            <h2 style="margin-bottom:15px; font-size:1.6rem; color:#2b3a67; font-weight:700;">Event Details</h2>
            <div id="userDetailsContent"
                style="flex:1; overflow-y:auto; padding:15px; font-size:1.05rem; border-radius:12px; background:#f1f4fb; color:#2b2b2b; white-space: pre-wrap;">
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:18px;">
                <button id="userDetailsCloseBtn"
                    style="padding:10px 22px; background:#e8ecf5; border:none; border-radius:10px; font-size:1rem; cursor:pointer; font-weight:600; color:#36415d;"
                    onmouseover="this.style.background='#d4d9e6'"
                    onmouseout="this.style.background='#e8ecf5'">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('eventSearch');
            const eventsWrapper = document.getElementById('eventsWrapper');
            const clearButton = document.querySelector('.dashboard_clear_btn');

            let currentFetchedEvents = [];

            // Function to build HTML string for all events
            const renderEvents = (events) => {
                if (events.length === 0) {
                    eventsWrapper.innerHTML = `<p>No events found.</p>`;
                    return;
                }

                // 1. Start the GRID Container
                let htmlContent = `<div class="dashboard_events_grid">`;

                // 2. Loop through events and create CARDS
                events.forEach((event, index) => {
                    const date = new Date(event.date).toLocaleDateString('en-US');

                    // Format start time
                    let startTime = 'N/A';
                    if (event.start_time) {
                        const [sh, sm] = event.start_time.split(':');
                        const sTime = new Date();
                        sTime.setHours(sh, sm, 0);
                        startTime = sTime.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    }

                    // Format end time
                    let endTime = 'N/A';
                    if (event.end_time) {
                        const [eh, em] = event.end_time.split(':');
                        const eTime = new Date();
                        eTime.setHours(eh, em, 0);
                        endTime = eTime.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    }

                    const departmentTag = event.department === 'OFFICES' ? event.office_name : event
                        .department;

                    // Handle target_year_levels
                    let yearLevelsText = event.target_users || 'No target group';
                    if (Array.isArray(event.target_year_levels) && event.target_year_levels.length >
                        0) {
                        yearLevelsText = event.target_year_levels.join(', ');
                    }

                    // Add the card to the html string
                    htmlContent += `
                        <div class="dashboard_event_card">
                            <div class="dashboard_event_title">${event.title}</div>
                            <div class="dashboard_event_details">
                                📅 ${date} &nbsp;&nbsp; 🕓 ${startTime} - ${endTime} &nbsp;&nbsp; 📍 ${event.location || 'N/A'}
                            </div>
                            <div class="dashboard_event_details">👥 ${yearLevelsText}</div>
                            <div class="dashboard_event_details">${event.description || 'No description provided.'}</div>
                            <div class="dashboard_event_details">${event.school_year || 'N/A'}</div>
                            <div class="dashboard_event_tags">
                                <span class="dashboard_tag dashboard_tag_admin">${departmentTag}</span>
                                <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                            </div>
                            <button class="dashboard_view_btn" data-index="${index}"
                                style="padding:10px 22px; background:#e8ecf5; border:none; border-radius:10px; font-size:1rem; cursor:pointer; font-weight:600; color:#36415d; margin-top:10px;">
                                👁️ View Details
                            </button>
                        </div>
                    `;
                });

                // 3. Close the GRID Container
                htmlContent += `</div>`;

                // 4. Inject into DOM
                eventsWrapper.innerHTML = htmlContent;
            };

            const fetchEvents = (query = '') => {
                fetch(`/usermanagement/dashboard/search?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        currentFetchedEvents = data.events;
                        renderEvents(currentFetchedEvents);
                    })
                    .catch(err => console.error(err));
            };

            // Initial Fetch
            fetchEvents();

            searchInput.addEventListener('input', () => fetchEvents(searchInput.value.trim()));
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                fetchEvents();
            });

            // Modal Logic
            const modal = document.getElementById('userDetailsModalOverlay');
            const modalContent = document.getElementById('userDetailsContent');
            const closeBtn = document.getElementById('userDetailsCloseBtn');

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('dashboard_view_btn')) {
                    const index = e.target.dataset.index;
                    let details = 'No additional details.';

                    // Check if data came from JS Fetch (index) or PHP Render (data-details)
                    if (index !== undefined && currentFetchedEvents[index]) {
                        details = currentFetchedEvents[index].more_details || details;
                    } else if (e.target.dataset.details) {
                        details = e.target.dataset.details;
                    }

                    modalContent.innerHTML = details;
                    modal.style.display = 'flex';
                }
            });

            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Department Modal Logic
            const deptBox = document.getElementById('dashboard_department_box');
            const deptModal = document.getElementById('dashboard_department_modal');
            const deptClose = document.getElementById('dashboard_close_modal');

            if (deptBox && deptModal) {
                deptBox.addEventListener('click', () => deptModal.style.display = 'flex');
                deptClose.addEventListener('click', () => deptModal.style.display = 'none');
                deptModal.addEventListener('click', (e) => {
                    if (e.target === deptModal) deptModal.style.display = 'none';
                });
            }
        });
    </script>
    @if (session('success'))
        <div id="toast"
            style="position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 22px; border-radius: 8px; z-index: 9999; opacity: 0; transition: opacity 0.5s;">
            {{ session('success') }}
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toast = document.getElementById('toast');
                if (toast) {
                    // Show the toast
                    toast.style.opacity = '1';

                    // Hide after 3 seconds
                    setTimeout(() => {
                        toast.style.opacity = '0';
                    }, 3000);
                }
            });
        </script>
    @endif
</x-usermanLayout>
