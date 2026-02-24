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
        <div class="activity-section">
            <div class="activity-header">
                <span>🔔 System Activity Tracking</span>
            </div>

            @forelse($logs as $log)
                <div class="activity-card-{{ $log->action_type }}" data-status="{{ $log->action_type }}">
                    <div class="activity-card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="icon">
                                @if ($log->action_type === 'created')
                                    ➕
                                @elseif($log->action_type === 'edited')
                                    ✏️
                                @elseif($log->action_type === 'cancelled')
                                    🗑️
                                @elseif($log->action_type === 'restored')
                                    🔄
                                @endif
                            </div>
                            <div class="info">
                                @php
                                    $title = $log->description['title'] ?? null;
                                @endphp
                                <h4>
                                    {{ ucfirst($log->action_type) }} Event:
                                    @if (is_array($title) && ($title['old'] ?? '') !== ($title['new'] ?? ''))
                                        <del>{{ $title['old'] ?? 'Untitled' }}</del> → {{ $title['new'] ?? 'Untitled' }}
                                    @else
                                        {{ is_array($title) ? $title['new'] ?? 'Untitled' : $title ?? 'Untitled' }}
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <span class="status">{{ $log->action_type }}</span>
                    </div>

                    <div class="activity-meta">
                        <p class="performed">
                            Action performed:
                            {{ $log->created_at->timezone('Asia/Manila')->format('m/d/Y, g:i A') }}
                        </p>

                        @if ($log->model_type === 'Event')
                            @php
                                $event_date = $log->description['event_date'] ?? null;
                                $start_time = $log->description['start_time'] ?? null;
                                $end_time = $log->description['end_time'] ?? null;
                                $location = $log->description['location'] ?? null;
                                $sections = $log->description['target_sections'] ?? null;
                                $faculty = $log->description['target_faculty'] ?? null;

                                if (empty($sections) || empty($faculty)) {
                                    $logEvent = \App\Models\Event::find($log->model_id);
                                    if ($logEvent) {
                                        if (empty($sections)) {
                                            $sections = $logEvent->target_sections ?? [];
                                        }
                                        if (empty($faculty)) {
                                            $facultyIds = $logEvent->target_faculty ?? [];
                                            if (is_string($facultyIds)) {
                                                $facultyIds = json_decode($facultyIds, true) ?? [];
                                            }
                                            $faculty = !empty($facultyIds)
                                                ? \App\Models\User::whereIn('id', $facultyIds)->pluck('name')->toArray()
                                                : [];
                                        }
                                    }
                                }

                                $formatList = function ($value) {
                                    if (is_array($value)) {
                                        return implode(', ', $value);
                                    }
                                    return $value ?? 'N/A';
                                };
                            @endphp

                            <p>📅 Event Date:</p>
                            <p>
                                @if (is_array($event_date) && ($event_date['old'] ?? '') !== ($event_date['new'] ?? ''))
                                    <del>{{ $event_date['old'] ?? 'N/A' }}</del> → {{ $event_date['new'] ?? 'N/A' }}
                                @else
                                    {{ is_array($event_date) ? $event_date['new'] ?? 'N/A' : $event_date ?? 'N/A' }}
                                @endif
                            </p>

                            <p>⏰ Time:</p>
                            <p>
                                @php
                                    $start_changed =
                                        is_array($start_time) &&
                                        ($start_time['old'] ?? '') !== ($start_time['new'] ?? '');
                                    $end_changed =
                                        is_array($end_time) && ($end_time['old'] ?? '') !== ($end_time['new'] ?? '');
                                @endphp

                                @if ($start_changed || $end_changed)
                                    <del>
                                        {{ $start_changed ? $start_time['old'] ?? 'N/A' : $start_time['new'] ?? 'N/A' }}
                                        –
                                        {{ $end_changed ? $end_time['old'] ?? 'N/A' : $end_time['new'] ?? 'N/A' }}
                                    </del>
                                    →
                                    {{ $start_time['new'] ?? ($start_time ?? 'N/A') }} –
                                    {{ $end_time['new'] ?? ($end_time ?? 'N/A') }}
                                @else
                                    {{ $start_time['new'] ?? ($start_time ?? 'N/A') }} –
                                    {{ $end_time['new'] ?? ($end_time ?? 'N/A') }}
                                @endif
                            </p>

                            <p>📍 Location:</p>
                            <p>
                                @if (is_array($location) && ($location['old'] ?? '') !== ($location['new'] ?? ''))
                                    <del>{{ $location['old'] ?? 'N/A' }}</del> → {{ $location['new'] ?? 'N/A' }}
                                @else
                                    {{ is_array($location) ? $location['new'] ?? 'N/A' : $location ?? 'N/A' }}
                                @endif
                            </p>

                            @php
                                $year_levels = $log->description['target_year_levels'] ?? null;
                                if (empty($year_levels)) {
                                    $logEvent = \App\Models\Event::find($log->model_id);
                                    if ($logEvent) {
                                        $year_levels = $logEvent->target_year_levels ?? [];
                                    }
                                }
                            @endphp

                            @if (!empty($year_levels))
                                <p>👤 Year Levels:</p>
                                <p>
                                    @if (is_array($year_levels) &&
                                            isset($year_levels['old'], $year_levels['new']) &&
                                            $year_levels['old'] !== $year_levels['new']
                                    )
                                        <del>{{ $formatList($year_levels['old']) }}</del> →
                                        {{ $formatList($year_levels['new']) }}
                                    @else
                                        {{ is_array($year_levels) && array_key_exists('new', $year_levels)
                                            ? $formatList($year_levels['new'])
                                            : $formatList($year_levels) }}
                                    @endif
                                </p>
                            @endif

                            @if (!empty($sections))
                                <p>🏫 Sections:</p>
                                <p>
                                    @if (is_array($sections) && isset($sections['old'], $sections['new']) && $sections['old'] !== $sections['new'])
                                        <del>{{ $formatList($sections['old']) }}</del> →
                                        {{ $formatList($sections['new']) }}
                                    @else
                                        {{ is_array($sections) && array_key_exists('new', $sections)
                                            ? $formatList($sections['new'])
                                            : $formatList($sections) }}
                                    @endif
                                </p>
                            @endif

                            @if (!empty($faculty))
                                <p>👩‍🏫 Faculty:</p>
                                <p>
                                    @if (is_array($faculty) && isset($faculty['old'], $faculty['new']) && $faculty['old'] !== $faculty['new'])
                                        <del>{{ $formatList($faculty['old']) }}</del> →
                                        {{ $formatList($faculty['new']) }}
                                    @else
                                        {{ is_array($faculty) && array_key_exists('new', $faculty)
                                            ? $formatList($faculty['new'])
                                            : $formatList($faculty) }}
                                    @endif
                                </p>
                            @endif
                        @endif
                    </div>

                    <div class="activity-details">
                        <strong>Description:</strong>
                        @php
                            $desc = $log->description['event_description'] ?? null;
                        @endphp
                        @if (is_array($desc) && ($desc['old'] ?? '') !== ($desc['new'] ?? ''))
                            <p><del>{{ $desc['old'] ?? 'N/A' }}</del> → {{ $desc['new'] ?? 'N/A' }}</p>
                        @else
                            <p>{{ is_array($desc) ? $desc['new'] ?? 'N/A' : $desc ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p>No activity found.</p>
            @endforelse
            <div class="pagination-wrapper">
                {{ $logs->links('vendor.pagination.simple') }}
            </div>
        </div>
    </body>

    </html>
</x-editorLayout>
