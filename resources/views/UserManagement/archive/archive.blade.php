<x-usermanLayout>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>eKalendaryo Archive</title> @vite(['resources/css/userman/archive.css', 'resources/js/userman/archive.js'])
    </head>
    <main class="archive-container">

        <div class="archive-header">
            <h2>User Archive</h2>
            <p>Archived user records (Dropped, Fired, Graduated)</p>
        </div>

        <form method="GET" class="archive-filters">
            <div class="filter-group">
                <label for="title">Filter by Title</label>
                <select name="title" id="title" onchange="this.form.submit()">
                    <option value="">All Titles</option>
                    @foreach ($titles as $t)
                        <option value="{{ $t }}" {{ $title === $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="school_year">Filter by School Year</label>
                <select name="school_year" id="school_year" onchange="this.form.submit()">
                    <option value="">All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ $schoolYear === (string) $sy->id ? 'selected' : '' }}>
                            {{ $sy->school_year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="department">Filter by Department</label>
                <select name="department" id="department" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="archive-table-wrapper">
            @if ($archivedUsers->count())
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Archived On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archivedUsers as $index => $user)
                            <tr>
                                <td>{{ $archivedUsers->firstItem() + $index }}</td>
                                <td>
                                    <div style="display:block; font-weight:bold;">
                                        {{ $user->title }}
                                    </div>
                                    {{ $user->name }}
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    {{ $user->department === 'OFFICES' ? $user->office_name ?? 'N/A' : $user->department ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $user->schoolYear?->school_year ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $user->status }}">
                                        {{ $user->yearlevel }} {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="archive-pagination">
                    {{ $archivedUsers->links('vendor.pagination.simple') }}
                </div>
            @else
                <p class="empty-state">No archived users found.</p>
            @endif
        </div>

    </main>
</x-usermanLayout>
