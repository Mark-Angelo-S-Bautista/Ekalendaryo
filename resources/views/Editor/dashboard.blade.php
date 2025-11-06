<x-editorLayout>
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

        <!-- Stats -->
        <section class="dashboard_stats">
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>My Events</h3>
                <p>{{ $myEventsCount }}</p>
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
                    <div class="dashboard_event_details">{{ $event->description ?? 'No description provided.' }}</div>
                    <div class="dashboard_event_details">{{ $event->school_year }}</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">
                            @if ($event->department === 'OFFICES')
                                {{ $event->user->title }}
                            @else
                                {{ $event->department }}
                            @endif
                        </span>
                        <span class="dashboard_tag dashboard_tag_upcoming">upcoming</span>
                    </div>
                </div>
            @empty
                <p>No upcoming events found.</p>
            @endforelse
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

</x-editorLayout>
