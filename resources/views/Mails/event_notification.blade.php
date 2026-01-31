<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Notification</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f7f5; font-family: Arial, Helvetica, sans-serif;">

    <!-- Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7f5;">
        <tr>
            <td align="center" style="padding:15px;">

                <!-- Main Card -->
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color:#1e7f43; padding:20px 15px;">
                            <img src="{{ asset('img/images.jpg') }}" alt="Bulacan Polytechnic College"
                                style="width:70px; max-width:100%; height:auto; display:block; margin-bottom:10px;">
                            <h2 style="color:#ffffff; margin:0; font-size:20px;">Bulacan Polytechnic College</h2>
                            <p style="color:#d9f2e4; margin:5px 0 0; font-size:14px;">
                                Malolos, Bulacan
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:20px 15px; color:#333333; font-size:16px; line-height:1.6;">

                            <p style="margin-top:0;">Hello <strong>{{ $student->name }}</strong>,</p>

                            @if ($isCancelled)
                                <div
                                    style="background-color:#fdecea; color:#b71c1c; padding:12px; border-radius:4px; font-size:15px;">
                                    <strong>NOTICE:</strong> This event has been <strong>CANCELLED</strong>.
                                </div>
                            @elseif ($isUpdate)
                                <div
                                    style="background-color:#fff8e1; color:#8d6e00; padding:12px; border-radius:4px; font-size:15px;">
                                    <strong>NOTICE:</strong> This event has been <strong>UPDATED</strong>.
                                </div>
                            @else
                                <div
                                    style="background-color:#e8f5e9; color:#1e7f43; padding:12px; border-radius:4px; font-size:15px;">
                                    <strong>NOTICE:</strong> A <strong>New Event</strong> has been posted.
                                </div>
                            @endif

                            <hr style="border:none; border-top:1px solid #e0e0e0; margin:20px 0;">

                            <h2 style="color:#1e7f43; font-size:20px; margin-bottom:10px;">
                                {{ $event->title }}
                                @if ($isUpdate && $event->title !== $oldEvent->title)
                                    <span style="color:#f9a825; font-size:14px;">(Changed)</span>
                                @endif
                            </h2>

                            <p>
                                <strong>Description:</strong><br>
                                {{ $event->description ?? 'No description provided.' }}
                            </p>

                            <p>
                                <strong>Date:</strong><br>
                                {{ \Carbon\Carbon::parse($event->date)->format('F j, Y') }}
                            </p>

                            <p>
                                <strong>Time:</strong><br>
                                {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                            </p>

                            <p>
                                <strong>Location:</strong><br>
                                {{ $event->location }}
                            </p>

                            <p><strong>School Year:</strong><br>{{ $event->school_year }}</p>

                            @if (!empty($event->target_year_levels))
                                <p><strong>Target Year
                                        Levels:</strong><br>{{ implode(', ', $event->target_year_levels) }}</p>
                            @else
                                <p><strong>Target Participant:</strong><br>{{ $event->target_users }}</p>
                            @endif

                            @if (!empty($event->more_details))
                                <p><strong>More Details:</strong></p>
                                <p style="white-space: pre-line;">
                                    {{ $event->more_details }}
                                </p>
                            @endif

                            <p style="margin-top:25px;">
                                Thank you,<br>
                                <strong>Bulacan Polytechnic College</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color:#f1f8f5; padding:15px; font-size:12px; color:#555;">
                            © {{ date('Y') }} Bulacan Polytechnic College
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
