<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Archived Events</title>
        @vite(['resources/css/editor/archive.css'])
    </head>

    <body>
        <main>
            <h2>Archived Events</h2>
            <p>All archived events from previous school years</p>

            {{-- FILTER --}}
            <form method="GET" style="margin: 20px 0;">
                <label for="school_year"><strong>Filter by School Year:</strong></label>
                <select name="school_year" id="school_year" onchange="this.form.submit()">
                    <option value="">All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy }}" {{ $schoolYear == $sy ? 'selected' : '' }}>
                            {{ $sy }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- TABLE --}}
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>School Year</th>
                        <th>Report</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($archivedEvents as $event)
                        <tr>
                            <td>{{ $event->title }}</td>

                            <td>{{ \Carbon\Carbon::parse($event->date)->format('F j, Y') }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                                -
                                {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                            </td>

                            <td>{{ $event->location }}</td>

                            <td>{{ $event->school_year }}</td>

                            <td data-label="Report">
                                @if ($event->report_path)
                                    <a href="{{ route('Editor.downloadReport', $event->id) }}"
                                        class="report-download-btn">
                                        📄 Download
                                    </a>
                                @else
                                    <span class="no-report">No Report</span>
                                @endif
                            </td>

                            <td>
                                @if ($event->status === 'archived')
                                    <span class="status archived">Archived</span>
                                @elseif ($event->status === 'cancelled')
                                    <span class="status cancelled">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">
                                No archived or cancelled events found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div style="margin-top: 20px;">
                {{ $archivedEvents->links('vendor.pagination.simple') }}
            </div>
        </main>
    </body>

    </html>
</x-editorLayout>
