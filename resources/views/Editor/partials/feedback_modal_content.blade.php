@if ($feedbacks->count())
    <div class="feedback-summary">
        <p><strong>Total Feedbacks:</strong> {{ $feedbacks->total() }}</p>
        <p><strong>Average Rating:</strong> {{ number_format($averageRating, 1) }} / 5★</p>
    </div>
    <ul class="feedback-list">
        @foreach ($feedbacks as $feedback)
            <div class="feedback-item">
                <div class="feedback-header">
                    👤<strong class="feedback-user">{{ trim($feedback->user->name) }} /
                        {{ $feedback->user->title }}</strong>
                    <span class="feedback-rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= $feedback->rating ? 'filled' : '' }}"
                                style="color: {{ $i <= $feedback->rating ? '#ffc107' : '#e0e0e0' }}; font-size: 1.2rem;">★</span>
                        @endfor
                    </span>
                    <span class="feedback-date">
                        {{ $feedback->created_at->timezone('Asia/Manila')->format('M d, Y • g:i A') }}
                    </span>
                </div>

                <div class="feedback-body" style="margin-top: 10px;">
                    <p><strong>Satisfaction:</strong> {{ $feedback->q_satisfaction }}</p>
                    <p><strong>Organization:</strong> {{ $feedback->q_organization }}</p>
                    <p><strong>Relevance:</strong> {{ $feedback->q_relevance }}</p>
                    <p><strong>Comment:</strong> {{ $feedback->comment }}</p>
                </div>
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
