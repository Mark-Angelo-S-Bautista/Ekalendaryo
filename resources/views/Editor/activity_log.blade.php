<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo - Admin - SMS</title>
        @vite(['resources/css/editor/activity_log.css', 'resources/js/editor/activity_log.js'])
    </head>

    <body>
        <div class="content">
            <div class="content-header">
                <h2>Activity Log</h2>
                <div class="filter-dropdown">
                    <select id="filterSelect" onchange="filterActivities()">
                        <option value="all">All</option>
                        <option value="created">Created</option>
                        <option value="edited">Edited</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
            </div>

            <div class="activity-section">
                <div class="activity-header">
                    <span>🔔 System Activity Tracking</span>
                </div>

                <div class="activity-card" data-status="created">
                    <div class="activity-card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="icon">➕</div>
                            <div class="info">
                                <h4>Event Created</h4>
                                <p>Event: dsadasdasdad</p>
                                <p>By: admin</p>
                            </div>
                        </div>
                        <span class="status">created</span>
                    </div>
                    <div class="activity-meta">
                        <p>📅 Event Date: 2025-11-06</p>
                        <p>⏰ Event Time: 17:45</p>
                        <p>📍 Location: BPC Court</p>
                        <p>👤 Event Type: admin</p>
                        <p class="performed">Action performed: 11/2/2025, 5:45:47 PM</p>
                    </div>
                    <div class="activity-details">
                        <strong>Event Details:</strong>
                        <p>Description: adadadasdad</p>
                    </div>
                </div>

                <div class="activity-card" data-status="edited">
                    <div class="activity-card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="icon">✏️</div>
                            <div class="info">
                                <h4>Event Edited</h4>
                                <p>Event: Meeting Schedule</p>
                                <p>By: admin</p>
                            </div>
                        </div>
                        <span class="status">edited</span>
                    </div>
                    <div class="activity-meta">
                        <p>📅 Event Date: 2025-11-07</p>
                        <p>⏰ Event Time: 09:00</p>
                        <p>📍 Location: Office Room</p>
                        <p>👤 Event Type: admin</p>
                        <p class="performed">Action performed: 11/7/2025, 9:00:00 AM</p>
                    </div>
                    <div class="activity-details">
                        <strong>Event Details:</strong>
                        <p>Description: Edited description sample</p>
                    </div>
                </div>

                <div class="activity-card" data-status="deleted">
                    <div class="activity-card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="icon">🗑️</div>
                            <div class="info">
                                <h4>Event Deleted</h4>
                                <p>Event: Old Event</p>
                                <p>By: admin</p>
                            </div>
                        </div>
                        <span class="status">deleted</span>
                    </div>
                    <div class="activity-meta">
                        <p>📅 Event Date: 2025-11-02</p>
                        <p>⏰ Event Time: 12:00</p>
                        <p>📍 Location: Auditorium</p>
                        <p>👤 Event Type: admin</p>
                        <p class="performed">Action performed: 11/2/2025, 12:00:00 PM</p>
                    </div>
                    <div class="activity-details">
                        <strong>Event Details:</strong>
                        <p>Description: This event was removed.</p>
                    </div>
                </div>

            </div>
        </div>
    </body>

    </html>
</x-editorLayout>
