@if ($paginator->hasPages())
  <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
    <div class="relative z-0 inline-flex pagination-links">
      @if ($paginator->onFirstPage())
        <span aria-disabled="true" aria-label="{{ __('Previous') }}">
          <span aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L9.06 10l3.71 3.71a.75.75 0 1 1-1.06 1.06l-4.24-4.24a.75.75 0 0 1 0-1.06l4.24-4.24a.75.75 0 0 1 1.08 0Z" clip-rule="evenodd" /></svg>
          </span>
        </span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">
          <span aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L9.06 10l3.71 3.71a.75.75 0 1 1-1.06 1.06l-4.24-4.24a.75.75 0 0 1 0-1.06l4.24-4.24a.75.75 0 0 1 1.08 0Z" clip-rule="evenodd" /></svg>
          </span>
        </a>
      @endif

      @php
        $startPage = max(1, $paginator->currentPage() - 1);
        $endPage = min($paginator->lastPage(), $startPage + 2);
        $startPage = max(1, $endPage - 2);
      @endphp

      @for ($page = $startPage; $page <= $endPage; $page++)
        @if ($page === $paginator->currentPage())
          <span aria-current="page"><span>{{ $page }}</span></span>
        @else
          <a href="{{ $paginator->url($page) }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
        @endif
      @endfor

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">
          <span aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd" /></svg>
          </span>
        </a>
      @else
        <span aria-disabled="true" aria-label="{{ __('Next') }}">
          <span aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd" /></svg>
          </span>
        </span>
      @endif
    </div>
  </nav>
@endif
