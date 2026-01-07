@if ($feedbacks->count())
    <ul class="feedback-list">
        @foreach ($feedbacks as $feedback)
            <ul class="feedback-item">
                <strong>{{ $feedback->user->name }}</strong>
                <span class="timestamp">
                    ({{ \Carbon\Carbon::parse($feedback->created_at)->timezone('Asia/Manila')->format('m/d/Y, g:i A') }})
                </span>
                <p>{{ $feedback->message }}</p>
            </ul>
        @endforeach
    </ul>

    {{-- Pagination --}}
    <div class="feedback-pagination">
        @if ($feedbacks->hasPages())
            <button class="prev-page" {{ $feedbacks->onFirstPage() ? 'disabled' : '' }}
                data-page="{{ $feedbacks->currentPage() - 1 }}">Previous</button>
            <span>Page {{ $feedbacks->currentPage() }} of {{ $feedbacks->lastPage() }}</span>
            <button class="next-page" {{ $feedbacks->currentPage() == $feedbacks->lastPage() ? 'disabled' : '' }}
                data-page="{{ $feedbacks->currentPage() + 1 }}">Next</button>
        @endif
    </div>
@else
    <p>No feedback yet.</p>
@endif
