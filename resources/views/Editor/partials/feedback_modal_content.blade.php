@if ($feedbacks->count())
    <ul class="feedback-list">
        @foreach ($feedbacks as $feedback)
            <div class="feedback-item">
                <div class="feedback-header">
                    👤<strong class="feedback-user">{{ trim($feedback->user->name) }}</strong>
                    <span class="feedback-date">
                        {{ $feedback->created_at->timezone('Asia/Manila')->format('M d, Y • g:i A') }}
                    </span>
                </div>

                <p class="feedback-message">
                    {{ $feedback->message }}
                </p>
            </div>
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
