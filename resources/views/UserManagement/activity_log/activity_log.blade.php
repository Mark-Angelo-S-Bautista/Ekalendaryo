<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo - Admin - SMS</title>
        @vite(['resources/css/userman/activityLog.css', 'resources/js/userman/activityLog.js'])

    </head>

    <body>

        <!-- Main Content -->
        <div class="content">
            <h2>Activity Log</h2>
            <div class="activity-section">
                <div class="activity-header">
                    <span>🔔 System Activity Tracking</span>
                </div>

                <!-- First Event -->
                <div class="activity-card">
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

                <!-- Second Event -->
                <div class="activity-card">
                    <div class="activity-card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="icon">➕</div>
                            <div class="info">
                                <h4>Event Created</h4>
                                <p>Event: dsad</p>
                                <p>By: admin</p>
                            </div>
                        </div>
                        <span class="status">created</span>
                    </div>
                    <div class="activity-meta">
                        <p>📅 Event Date: 2025-11-05</p>
                        <p>⏰ Event Time: 15:55</p>
                        <p>📍 Location: COURT</p>
                        <p>👤 Event Type: admin</p>
                        <p class="performed">Action performed: 11/2/2025, 3:56:00 PM</p>
                    </div>
                    <div class="activity-details">
                        <strong>Event Details:</strong>
                        <p>Description: adadadds</p>
                    </div>
                </div>
            </div>
        </div>
        </div>


    </body>

    </html>
</x-usermanLayout>
