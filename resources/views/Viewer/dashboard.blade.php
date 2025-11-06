<x-viewerLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo - Admin Dashboard</title>
        @vite(['resources/css/viewer/viewerdashboard.css'])
    </head>

    <body>
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
                        <div class="dashboard_event_details">{{ $event->description ?? 'No description provided.' }}
                        </div>
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

                            // Reset header
                            eventContainer.innerHTML = `
                    <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
                    <p>Next 2 upcoming events (within 30 days)</p>
                `;

                            if (events.length === 0) {
                                eventContainer.innerHTML += `<p>No events found.</p>`;
                                return;
                            }

                            // Render matching events
                            events.forEach(event => {
                                const date = new Date(event.date).toLocaleDateString('en-US');
                                const startTime = event.start_time ? event.start_time.substring(0,
                                    5) : '';
                                const endTime = event.end_time ? event.end_time.substring(0, 5) :
                                    '';

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
        </div>


    </body>

    </html>
</x-viewerLayout>
