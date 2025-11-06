<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKalendaryo</title>
    <script>
        const events = @json($events);
        console.log("Loaded events:", events);
    </script>
    @vite(['resources/css/userman/calendar.css', 'resources/js/userman/calendar.js'])

</head>

<body>

    <div class="calendar_container">
        <div class="calendar_header">
            <h2>Centralized Calendar</h2>
            <div class="calendar_controls">
                <div class="calendar_month-navigation">
                    <button id="calendar_prevMonth">&#9664;</button>
                    <span id="calendar_monthYear">November 2025</span>
                    <button id="calendar_nextMonth">&#9654;</button>
                </div>
                <div class="calendar_event-filter">
                    <select id="calendar_eventFilter">
                        <option value="all">All Events</option>
                        <option value="department">Department Events</option>
                        <option value="studentgov">Student Government</option>
                        <option value="sports">Sports Events</option>
                        <option value="admin">Admin Events</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ✅ Day Headers -->
        <div class="calendar_day-headers">
            <div>Su</div>
            <div>Mo</div>
            <div>Tu</div>
            <div>We</div>
            <div>Th</div>
            <div>Fr</div>
            <div>Sa</div>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar_grid" id="calendar_grid"></div>

    </div>

    <!-- Modal -->
    <div class="calendar_modal" id="calendar_modal">
        <div class="calendar_modal-content" id="calendar_modal-content">
            <div class="calendar_modal-header">
                <h3 id="calendar_modal-title"></h3>
                <span class="calendar_close" id="calendar_close">&times;</span>
            </div>
            <div id="calendar_modal-body"></div>
        </div>
    </div>

</body>

</html>
