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
                    </select>
                </div>
            </div>
        </div>

        <!-- More Details Modal -->
        <div id="moreDetailsModal" class="modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:9999; justify-content:center; align-items:center; padding:20px;">
            <div
                style="background:#ffffff; padding:30px; width:85%; max-width:1000px; height:75%; border-radius:18px; box-shadow:0 12px 45px rgba(0,0,0,0.30); display:flex; flex-direction:column; border-top:6px solid #2b5eff;">
                <div id="moreDetailsContent"
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
