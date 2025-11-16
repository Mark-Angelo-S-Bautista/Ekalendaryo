<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Event Notification</title>
</head>

<body>
    <h2>Hello {{ $student->name }},</h2>

    @if ($isCancelled)
        <p><strong style="color: blue;">NOTICE:</strong> This event has been <span style="color: red;">CANCELLED</span>.
        </p>
    @elseif ($isUpdate)
        <p><strong style="color: blue;">NOTICE:</strong> This event has been <span style="color: yellow;">UPDATED</span>.
        </p>
    @else
        <p><strong style="color: blue;">NOTICE:</strong> <span style="color: green;">You have a New Event Posted.</span>
        </p>
    @endif

    <h3>{{ $event->title }}
        @if ($isUpdate && $event->title !== $oldEvent->title)
            <span style="color: yellow;">(Changed)</span>
        @endif
    </h3>

    <p><strong>Description:</strong>
        {{ $event->description ?? 'No description provided.' }}
        @if ($isUpdate && $event->description !== $oldEvent->description)
            <span style="color: yellow;">(Changed)</span>
        @endif
    </p>

    <p><strong>Date:</strong>
        {{ \Carbon\Carbon::parse($event->date)->format('F j, Y') }}
        @if ($isUpdate && $event->date !== $oldEvent->date)
            <span style="color: yellow;">(Changed)</span>
        @endif
    </p>

    <p><strong>Time:</strong>
        {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} -
        {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
        @if ($isUpdate && ($event->start_time !== $oldEvent->start_time || $event->end_time !== $oldEvent->end_time))
            <span style="color: yellow;">(Changed)</span>
        @endif
    </p>

    <p><strong>Location:</strong> {{ $event->location }}
        @if ($isUpdate && $event->location !== $oldEvent->location)
            <span style="color: yellow;">(Changed)</span>
        @endif
    </p>

    <p><strong>School Year:</strong> {{ $event->school_year }}</p>

    @if (!empty($event->target_year_levels))
        <p><strong>Target Year Levels:</strong> {{ implode(', ', $event->target_year_levels) }}</p>
    @else
        <p><strong>Target Participant:</strong> {{ $event->target_users }}</p>
    @endif

    @if (!empty($event->more_details))
        <p><strong>More Details:</strong></p>
        <p style="white-space: pre-line;">
            {{ $event->more_details }}
            @if ($isUpdate && $event->more_details !== $oldEvent->more_details)
                <span style="color: yellow;">(Updated)</span>
            @endif
        </p>
    @endif

    <br>
    <p>Thank you.</p>
</body>

</html>
