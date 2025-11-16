<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Event Notification</title>
</head>

<body>
    <h2>Hello {{ $student->name }},</h2>

    <p>You have a new event from your department:</p>

    <h3>{{ $event->title }}</h3>

    <p><strong>Description:</strong> {{ $event->description ?? 'No description provided.' }}</p>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($event->date)->format('F j, Y') }}</p>
    <p><strong>Time:</strong>
        {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} -
        {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
    </p>
    <p><strong>Location:</strong> {{ $event->location }}</p>
    <p><strong>School Year:</strong> {{ $event->school_year }}</p>

    @if (!empty($event->target_year_levels))
        <p><strong>Target Year Levels:</strong> {{ implode(', ', $event->target_year_levels) }}</p>
    @else
        <p><strong>Target Year Levels:</strong> All students in your department</p>
    @endif

    @if (!empty($event->more_details))
        <p><strong>More Details:</strong></p>
        <p style="white-space: pre-line;">{{ $event->more_details }}</p>
    @endif

    <br>
    <p>Thank you.</p>
</body>

</html>
