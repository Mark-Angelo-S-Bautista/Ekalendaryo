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
                        // Determine dynamic status
                        $status = match ($event->status) {
                            'cancelled' => 'cancelled',
                            'completed' => 'completed',
                            'ongoing' => 'ongoing',
                            default => 'upcoming',
                        };
                        
                        // Check if this is a new notification
                        $isNew = !$lastViewed || $event->created_at > $lastViewed || $event->updated_at > $lastViewed;
                    @endphp

                    <div class="notif-card {{ $status }}{{ $isNew ? ' new-notification' : '' }}">
                        <div class="notif-info">

                            <p class="notif-heading">
                                {{ ucfirst($status) }} Event: {{ $event->title }}
                            </p>

                            <p class="notif-sub">
                                @switch($status)
                                    @case('upcoming')
                                        A new event is scheduled
                                    @break

                                    @case('completed')
                                        This event has been completed
                                    @break

                                    @case('canceled')
                                        This event was canceled
                                    @break
                                @endswitch
                            </p>

                            <div class="notif-details">
                                <p>📅 {{ $event->date }}</p>
                                <p>📍 {{ $event->location }}</p>
                                <p>🕒 {{ $event->start_time }}</p>
                                <p>👤 {{ $event->department ?? 'Admin' }}</p>
                                <p>🎓
                                    {{ is_array($event->target_year_levels) ? implode(', ', $event->target_year_levels) : $event->target_year_levels }}
                                </p>
                                @php
                                    $eventSections = $event->target_sections;
                                    if (is_string($eventSections)) {
                                        $eventSections = json_decode($eventSections, true) ?? [];
                                    } elseif (!is_array($eventSections)) {
                                        $eventSections = [];
                                    }

                                    $eventFacultyIds = $event->target_faculty;
                                    if (is_string($eventFacultyIds)) {
                                        $eventFacultyIds = json_decode($eventFacultyIds, true) ?? [];
                                    } elseif (!is_array($eventFacultyIds)) {
                                        $eventFacultyIds = [];
                                    }

                                    $eventFacultyNames = [];
                                    if (!empty($eventFacultyIds)) {
                                        $eventFacultyNames = \App\Models\User::whereIn('id', $eventFacultyIds)
                                            ->pluck('name')
                                            ->toArray();
                                    }
                                @endphp
                                @if (!empty($eventSections))
                                    <span>🏫 {{ implode(', ', $eventSections) }}</span>
                                @endif
                                @if (!empty($eventFacultyNames))
                                    <span>👩‍🏫 {{ implode(', ', $eventFacultyNames) }}</span>
                                @endif
                            </div>

                        </div>

                        <!-- Dynamic Status Tag -->
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
