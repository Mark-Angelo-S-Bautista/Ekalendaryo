<x-usermanLayout>
    <div class="dashboard_container">
        <!-- Welcome Card -->
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
                <p class="dashboard_school_year">Current School Year: {{ $currentSchoolYearName }}</p>
            </div>
            <form id="changeSchoolYearForm" action="{{ url('/usermanagement/dashboard') }}" method="POST">
                @csrf
                <button type="submit" class="dashboard_change_year_btn">
                    Change School Year
                </button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const changeYearForm = document.getElementById('changeSchoolYearForm');

                    changeYearForm.addEventListener('submit', (e) => {
                        const confirmed = confirm(
                            '⚠️ Are you sure you want to change the school year? This action may affect event visibility.'
                        );
                        if (!confirmed) {
                            e.preventDefault(); // Stop form submission if user clicks "Cancel"
                        }
                    });
                });
            </script>
        </section>

        <!-- Search Box -->
        <section class="dashboard_search_card">
            <div class="dashboard_search_box">
                <input type="text" id="eventSearch" placeholder="Search events..." class="dashboard_search_input">
                <button class="dashboard_clear_btn">Clear</button>
            </div>
        </section>

        <!-- Stats -->
        <section class="dashboard_stats">
            <div class="dashboard_stat_box dashboard_clickable" id="dashboard_department_box">
                <h3>Total Departments and Offices Events</h3>
                <p>{{ $departmentCounts->sum() }}</p>
            </div>
        </section>

        <!-- Upcoming Events -->
        <section class="dashboard_upcoming_card">
            <h3 class="dashboard_upcoming_title">Upcoming Events</h3>
            <p>For the next 30 days</p>
            <div id="eventsWrapper">
                <p>Loading events...</p>
            </div>
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

    <!-- Event Details Modal -->
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

            const statusMap = {
                upcoming: {
                    text: 'upcoming',
                    class: 'dashboard_tag_upcoming'
                },
                ongoing: {
                    text: 'ongoing',
                    class: 'dashboard_tag_ongoing'
                },
                completed: {
                    text: 'completed',
                    class: 'dashboard_tag_completed'
                },
                cancelled: {
                    text: 'cancelled',
                    class: 'dashboard_tag_cancelled'
                }
            };

            const renderEvents = (events) => {
                if (!events.length) {
                    eventsWrapper.innerHTML = `<p>No upcoming events.</p>`;
                    return;
                }

                let htmlContent = `<div class="dashboard_events_grid">`;
                events.forEach((event, index) => {
                    const date = new Date(event.date).toLocaleDateString('en-US');

                    const formatTime = t => {
                        if (!t) return 'N/A';
                        const [h, m] = t.split(':');
                        const dt = new Date();
                        dt.setHours(h, m, 0);
                        return dt.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    };
                    const startTime = formatTime(event.start_time);
                    const endTime = formatTime(event.end_time);

                    const departmentTag = event.department === 'OFFICES' ? event.office_name : event
                        .department;

                    let yearLevelsText = 'No target group';
                    if (Array.isArray(event.target_year_levels) && event.target_year_levels.length >
                        0) {
                        yearLevelsText = event.target_year_levels.join(', ');
                    } else if (event.target_users) {
                        yearLevelsText = event.target_users;
                    }

                    let sectionsText = '';
                    if (Array.isArray(event.target_sections) && event.target_sections.length > 0) {
                        sectionsText = event.target_sections.join(', ');
                    } else if (typeof event.target_sections === 'string') {
                        try {
                            const parsed = JSON.parse(event.target_sections);
                            if (Array.isArray(parsed) && parsed.length > 0) {
                                sectionsText = parsed.join(', ');
                            }
                        } catch (e) {
                            sectionsText = event.target_sections;
                        }
                    }

                    // Determine dynamic status tag
                    const rawStatus = event.computed_status || event.status || 'upcoming';
                    const normalizedStatus = String(rawStatus).toLowerCase();

                    const status = statusMap[normalizedStatus] || {
                        text: rawStatus,
                        class: 'dashboard_tag_upcoming'
                    };

                    htmlContent += `
                        <div class="dashboard_event_card">
                            <div class="dashboard_event_title">${event.title}</div>
                            <div class="dashboard_event_details">
                                📅 ${date} &nbsp;&nbsp; 🕓 ${startTime} - ${endTime} &nbsp;&nbsp; 📍 ${event.location || 'N/A'}
                            </div>
                            <div class="dashboard_event_details">👥 ${yearLevelsText}</div>
                            ${sectionsText ? `<div class="dashboard_event_details">🏫 ${sectionsText}</div>` : ''}
                            <div class="dashboard_event_details" style="max-width:100%; white-space:normal; word-break:break-word; overflow-wrap:anywhere;">
                                ${event.description || 'No description provided.'}
                            </div>
                            <div class="dashboard_event_details">SY.${event.school_year || 'N/A'}</div>
                            <div class="dashboard_event_tags">
                                <span class="dashboard_tag dashboard_tag_admin">${departmentTag}</span>
                                <span class="dashboard_tag ${status.class}">${status.text}</span>
                            </div>
                            <button class="dashboard_view_btn" data-index="${index}"
                                style="padding:10px 22px; background:#e8ecf5; border:none; border-radius:10px; font-size:1rem; cursor:pointer; font-weight:600; color:#36415d; margin-top:10px;">
                                👁️ View Details
                            </button>
                        </div>
                    `;
                });
                htmlContent += `</div>`;
                eventsWrapper.innerHTML = htmlContent;
            };

            const fetchEvents = (query = '') => {
                fetch(`/usermanagement/dashboard/search?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {

                        currentFetchedEvents = data.events.filter(event => {
                            const status = String(
                                event.computed_status || event.status || ''
                            ).toLowerCase();

                            // ✅ explicitly allow only these
                            return status.includes('upcoming') || status.includes('ongoing');
                        });

                        renderEvents(currentFetchedEvents);
                    })
                    .catch(err => console.error(err));
            };

            // Initial fetch
            fetchEvents();

            searchInput.addEventListener('input', () => fetchEvents(searchInput.value.trim()));
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                fetchEvents();
            });

            // Event Details Modal
            const modal = document.getElementById('userDetailsModalOverlay');
            const modalContent = document.getElementById('userDetailsContent');
            const closeBtn = document.getElementById('userDetailsCloseBtn');

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('dashboard_view_btn')) {
                    const index = e.target.dataset.index;
                    const details = currentFetchedEvents[index]?.more_details || 'No additional details.';
                    modalContent.innerHTML = details;
                    modal.style.display = 'flex';
                }
            });

            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Department Modal
            const deptBox = document.getElementById('dashboard_department_box');
            const deptModal = document.getElementById('dashboard_department_modal');
            const deptClose = document.getElementById('dashboard_close_modal');

            if (deptBox && deptModal) {
                deptBox.addEventListener('click', () => deptModal.style.display = 'flex');
                deptClose.addEventListener('click', () => deptModal.style.display = 'none');
                deptModal.addEventListener('click', e => {
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
                    toast.style.opacity = '1';
                    setTimeout(() => {
                        toast.style.opacity = '0';
                    }, 3000);
                }
            });
        </script>
    @endif
</x-usermanLayout>
