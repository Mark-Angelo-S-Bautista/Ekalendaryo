<x-viewerLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo - Notifications</title>
        @vite(['resources/css/viewer/notifications.css'])

    </head>

    <body>

        <!-- Header (Outside the Container) -->
        <div class="page-header">
            <h1>Notifications</h1>
            <span class="notif-count">10 notifications</span>
        </div>

        <!-- Notifications Container -->
        <div class="container">
            <div class="notif-title">
                <span class="bell">🔔</span>
                <h2>All Notifications</h2>
            </div>
            <p class="subtitle">Event updates, changes, and your registered events</p>

            <div class="notif-list">

                <!-- Created Event 1 -->
                <div class="notif-card created">
                    <div class="notif-info">
                        <p class="notif-heading">Event Created: dsadasdasdad</p>
                        <p class="notif-sub">A new event has been scheduled</p>
                        <div class="notif-details">
                            <p>📅 2025-11-06</p>
                            <p>📍 BPC Court</p>
                            <p>🕒 17:45</p>
                            <p>👤 By admin</p>
                            <p>🗓️ 11/2/2025</p>
                        </div>
                    </div>
                    <span class="status created-status">created</span>
                </div>

                <!-- Created Event 2 -->
                <div class="notif-card created">
                    <div class="notif-info">
                        <p class="notif-heading">Event Created: dsad</p>
                        <p class="notif-sub">A new event has been scheduled</p>
                        <div class="notif-details">
                            <p>📅 2025-11-05</p>
                            <p>📍 COURT</p>
                            <p>🕒 15:55</p>
                            <p>👤 By admin</p>
                            <p>🗓️ 11/2/2025</p>
                        </div>
                    </div>
                    <span class="status created-status">created</span>
                </div>

                <!-- Completed Event 1 -->
                <div class="notif-card completed">
                    <div class="notif-info">
                        <p class="notif-heading">Completed Event: CS Department Graduation Ceremony</p>
                        <p class="notif-sub">You attended this event</p>
                        <div class="notif-details">
                            <p>📅 2025-09-25</p>
                            <p>📍 CS Auditorium</p>
                            <p>🕒 10:00 - 14:00</p>
                            <p>👤 By CS Department Head</p>
                        </div>
                    </div>
                    <span class="status completed-status">completed</span>
                </div>

                <!-- Completed Event 2 -->
                <div class="notif-card completed">
                    <div class="notif-info">
                        <p class="notif-heading">Completed Event: Basketball Tournament</p>
                        <p class="notif-sub">You attended this event</p>
                        <div class="notif-details">
                            <p>📅 2025-09-10</p>
                            <p>📍 School Gymnasium</p>
                            <p>🕒 15:00 - 17:00</p>
                            <p>👤 By Sports Adviser</p>
                        </div>
                    </div>
                    <span class="status completed-status">completed</span>
                </div>

                <!-- Completed Event 3 -->
                <div class="notif-card completed">
                    <div class="notif-info">
                        <p class="notif-heading">Completed Event: Student Government Assembly</p>
                        <p class="notif-sub">You attended this event</p>
                        <div class="notif-details">
                            <p>📅 2025-09-05</p>
                            <p>📍 Main Auditorium</p>
                            <p>🕒 10:00 - 12:00</p>
                            <p>👤 By SG Adviser</p>
                        </div>
                    </div>
                    <span class="status completed-status">completed</span>
                </div>

            </div>
        </div>

    </body>

    </html>
</x-viewerLayout>
