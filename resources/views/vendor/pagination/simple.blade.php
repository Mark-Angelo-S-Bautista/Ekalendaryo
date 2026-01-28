@if ($paginator->hasPages())
    <nav class="simple-pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="disabled">Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">Prev</a>
        @endif

        {{-- Current / Total --}}
        <span class="current-page">
            {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </span>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Next</a>
        @else
            <span class="disabled">Next</span>
        @endif
    </nav>
@endif
