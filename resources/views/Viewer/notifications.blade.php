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

        <!-- Page Header -->
        <div class="page-header">
            <h1>🔔 Notifications</h1>
            <span class="notif-count">{{ $events->total() }} notifications</span>
        </div>

        <!-- Main Container -->
        <div class="container">
            <h2 class="subtitle">Events Upcoming, Completed, and Canceled</h2>

            <div class="notif-list">

                @forelse ($events as $event)
                    @php
                        // Determine status for CSS colors
                        // Upcoming = future events, Completed = past events, Canceled = canceled
                        if ($event->status === 'canceled') {
                            $status = 'canceled';
                        } elseif ($event->status === 'completed') {
                            $status = 'completed';
                        } else {
                            $status = 'upcoming'; // anything else is upcoming
                        }

                        // Notification description
                        $description = match ($status) {
                            'upcoming' => 'A new event is scheduled',
                            'completed' => 'This event has been completed',
                            'canceled' => 'This event was canceled',
                        };
                    @endphp

                    <div class="notif-card {{ $status }}">
                        <div class="notif-info">

                            <p class="notif-heading">
                                {{ ucfirst($status) }} Event: {{ $event->title }}
                            </p>

                            <p class="notif-sub">
                                {{ $description }}
                            </p>

                            <div class="notif-details">
                                <p>📅 {{ $event->date }}</p>
                                <p>📍 {{ $event->location }}</p>
                                <p>🕒 {{ $event->start_time }}</p>
                                <p>👤 {{ $event->department ?? 'Admin' }}</p>
                                <p>🎓
                                    {{ is_array($event->target_year_levels) ? implode(', ', $event->target_year_levels) : $event->target_year_levels }}
                                </p>
                            </div>

                        </div>

                        <span class="status {{ $status }}-status">
                            {{ $status }}
                        </span>
                    </div>

                @empty
                    <p class="empty">No notifications available.</p>
                @endforelse

            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $events->links('vendor.pagination.simple') }}
            </div>

        </div>

    </body>

    </html>
</x-viewerLayout>
