<x-usermanLayout>
    <div class="dashboard_container">
        <!-- Welcome -->
        <section class="dashboard_welcome_card">
            <div>
                <h2>Welcome back, {{ $user->name ?? 'User' }}!</h2>
                <p>{{ $user->title ?? 'Admin Dashboard' }} Dashboard</p>
                {{-- <p class="dashboard_school_year">Current School Year: {{ $currentSchoolYear }}</p> --}}
            </div>
            <button class="dashboard_change_year_btn">Change School Year</button>
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
                        📅 {{ \Carbon\Carbon::parse($event->date)->format('m/d/Y') }}
                        &nbsp;&nbsp; 🕓 {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                        {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                        &nbsp;&nbsp; 📍 {{ $event->location }}
                    </div>
                    <div class="dashboard_event_details">{{ $event->description ?? 'No description provided.' }}</div>
                    <div class="dashboard_event_details">{{ $event->school_year }}</div>
                    <div class="dashboard_event_tags">
                        <span class="dashboard_tag dashboard_tag_admin">{{ $event->department }}</span>
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
</x-usermanLayout>
