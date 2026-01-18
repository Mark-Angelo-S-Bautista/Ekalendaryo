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
            <h1>Notifications</h1>
            <span class="notif-count">{{ $events->total() }} notifications</span>
        </div>

        <!-- Main Container -->
        <div class="container">

            <div class="notif-title">
                <span class="bell">🔔</span>
                <h2>All Notifications</h2>
            </div>

            <p class="subtitle">Event updates, changes, and your registered events</p>

            <div class="notif-list">

                @forelse ($events as $event)
                    @php
                        // Normalize status for CSS matching
                        $status = strtolower($event->status ?? 'created');

                        // Handle cancelled vs canceled mismatch
                        if ($status === 'cancelled') {
                            $status = 'canceled';
                        }
                    @endphp

                    <div class="notif-card {{ $status }}">
                        <div class="notif-info">

                            <p class="notif-heading">
                                {{ ucfirst($status) }} Event: {{ $event->title }}
                            </p>

                            <p class="notif-sub">
                                @if ($status === 'created')
                                    A new event has been scheduled
                                @elseif ($status === 'updated')
                                    Event details were updated
                                @elseif ($status === 'completed')
                                    This event has been completed
                                @elseif ($status === 'canceled')
                                    This event was canceled
                                @endif
                            </p>

                            <div class="notif-details">
                                <p>📅 {{ $event->date }}</p>
                                <p>📍 {{ $event->location }}</p>
                                <p>🕒 {{ $event->start_time }}</p>
                                <p>👤 {{ $event->department ?? 'Admin' }}</p>
                                <p>🗓️ {{ $event->updated_at->format('m/d/Y') }}</p>
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
