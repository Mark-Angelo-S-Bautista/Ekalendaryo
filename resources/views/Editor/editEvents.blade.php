<x-editorLayout>

    <head>
        @vite(['resources/css/editor/editEvents.css'])
    </head>
    <div class="edit-event-container">
        <h1>Edit Event</h1>

        <form action="{{ route('Editor.update', $event->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Event title</label>
                <input type="text" name="title" value="{{ $event->title }}" required>
            </div>

            <div class="form-group">
                <label>Event description</label>
                <textarea name="description">{{ $event->description }}</textarea>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $event->date }}" required>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" value="{{ $event->start_time }}" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" value="{{ $event->end_time }}" required>
            </div>

            <div class="form-group">
                <label>Event location</label>
                <input type="text" name="location" value="{{ $event->location }}" required>
            </div>

            <div class="form-group">
                <label>Target Year Levels</label>
                <div class="checkbox-group">
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                        <div class="checkbox-inline">
                            <input type="checkbox" name="target_year_levels[]" value="{{ $year }}"
                                {{ in_array($year, $event->target_year_levels ?? []) ? 'checked' : '' }}>
                            {{ $year }}
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-update">Update Event</button>
            <a href="{{ route('Editor.index') }}" class="btn-cancel">Cancel</a>
        </form>
    </div>
</x-editorLayout>
