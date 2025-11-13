<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKalendaryo</title>

    <script id="calendar-event-data" type="application/json">
        @json($events)
    </script>

    @vite(['resources/css/userman/calendar.css', 'resources/js/userman/calendar.js'])

    <script>
        // This function checks if the page is being loaded from the browser's bfcache.
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>

<script>
    // This function checks if the page is being loaded from the browser's bfcache.
    window.addEventListener('pageshow', function(event) {
        // persisted == true means the page was loaded from the bfcache
        if (event.persisted) {
            // Force a hard reload, which forces the browser to make a fresh server request.
            window.location.reload();
        }
    });
</script>

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

        <!-- More Details Modal -->
        <div id="moreDetailsModal" class="modal"
            style="display:none;
                    position:fixed;
                    top:0;
                    left:0;
                    width:100%;
                    height:100%;
                    background:rgba(0,0,0,0.5);
                    justify-content:center;
                    align-items:center;
                    z-index:1050;
                    white-space: pre-wrap;">
            <div
                style="background:#fff;
                        padding:20px;
                        border-radius:8px;
                        max-width:600px;
                        width:90%;
                        max-height:80vh;
                        overflow-y:auto;
                        position:relative;
                        z-index:1060;">
                <button id="closeMoreDetailsBtn"
                    style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
                <div id="moreDetailsContent"></div>
            </div>
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
