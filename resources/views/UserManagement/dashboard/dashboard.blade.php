<x-usermanLayout>
    <div class="dashboard_container">
        <!-- Welcome -->
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
                {{-- <p class="dashboard_school_year">Current School Year: {{ $currentSchoolYear }}</p> --}}
            </div>
            <button class="dashboard_change_year_btn">Change School Year</button>
        </section>

        <!-- Search -->
        <section class="dashboard_search_card">
            <div class="dashboard_search_box">
                <input type="text" id="eventSearch" placeholder="Search events..." class="dashboard_search_input">
                <button class="dashboard_clear_btn">Clear</button>
            </div>

        </section>

        <!-- Stats -->
        <section class="dashboard_stats">
            <div class="dashboard_stat_box">
                <h3>Total Events</h3>
                <p>{{ $totalEvents }}</p>
            </div>
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>Department and Offices Events</h3>
                <p>{{ $departmentCounts->sum() }}</p>
            </div>
        </section>

        <!-- Upcoming Events -->
        <section class="dashboard_upcoming_card">
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
            <p>Next upcoming events (within 30 days)</p>

            @forelse ($upcomingEvents as $event)
                <div class="dashboard_event_card">
                    <div class="dashboard_event_title">{{ $event->title }}</div>
                    <div class="dashboard_event_details">
                        📅 {{ \Carbon\Carbon::parse($event->date)->format('n/j/Y') }}
                        &nbsp;&nbsp; 🕓 {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                        {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                        &nbsp;&nbsp; 📍 {{ $event->location }}
                    </div>
                    <div class="dashboard_event_details">{{ $event->description ?? 'No description provided.' }}</div>
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
                </div>
            @empty
                <p>No upcoming events.</p>
            @endforelse
        </section>
    </div>

    <!-- Department Modal -->
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('eventSearch');
            const eventContainer = document.querySelector('.dashboard_upcoming_card');
            const clearButton = document.querySelector('.dashboard_clear_btn');

            const renderEvents = (events) => {
                eventContainer.innerHTML = `
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
            <p>Upcoming events (within 30 days)</p>
        `;

                if (events.length === 0) {
                    eventContainer.innerHTML += `<p>No events found.</p>`;
                    return;
                }

                events.forEach(event => {
                    const date = new Date(event.date).toLocaleDateString('en-US');
                    const startTime = event.start_time ? event.start_time.substring(0, 5) : '';
                    const endTime = event.end_time ? event.end_time.substring(0, 5) : '';

                    eventContainer.innerHTML += `
                <div class="dashboard_event_card">
                    <div class="dashboard_event_title">${event.title}</div>
                    <div class="dashboard_event_details">
                        📅 ${date} &nbsp; 🕓 ${startTime} - ${endTime} &nbsp; 📍 ${event.location || ''}
                    </div>
                    <div class="dashboard_event_details">${event.description || ''}</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">${event.department}</span>
                        <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                    </div>
                </div>
            `;
                });
            };

            const fetchEvents = (query = '') => {
                fetch(`/usermanagement/dashboard/search?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => renderEvents(data.events))
                    .catch(err => console.error(err));
            };

            // Initial fetch to show all events
            fetchEvents();

            // Search as you type
            searchInput.addEventListener('input', () => fetchEvents(searchInput.value.trim()));

            // Clear search
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                fetchEvents();
            });
        });
    </script>
</x-usermanLayout>
