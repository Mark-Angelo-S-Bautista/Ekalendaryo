@if ($paginator->hasPages())
    <nav class="simple-pagination"
        style="display:flex;align-items:center;justify-content:center;gap:8px;margin:20px 0;font-family:Inter, sans-serif;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="disabled"
                style="padding:6px 12px;border:1px solid #ddd;border-radius:6px;color:#999;background:#f5f5f5;">Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                style="padding:6px 12px;border:1px solid #006400;border-radius:6px;color:#006400;text-decoration:none;background:#fff;">Prev</a>
        @endif

        {{-- Current / Total --}}
        <span class="current-page"
            style="padding:6px 12px;border:1px solid #ddd;border-radius:6px;background:#fff;color:#333;">
            {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </span>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                style="padding:6px 12px;border:1px solid #006400;border-radius:6px;color:#006400;text-decoration:none;background:#fff;">Next</a>
        @else
            <span class="disabled"
                style="padding:6px 12px;border:1px solid #ddd;border-radius:6px;color:#999;background:#f5f5f5;">Next</span>
        @endif
    </nav>
@endif
