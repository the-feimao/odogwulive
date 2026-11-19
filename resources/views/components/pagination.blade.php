@props([
    'paginator',
    'onEachSide' => 1,
])

@if ($paginator && $paginator->hasPages() && method_exists($paginator, 'lastPage'))
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $side = (int) $onEachSide;

        $start = max(1, $current - $side);
        $end = min($last, $current + $side);

        if ($current <= $side + 1) {
            $end = min($last, $side * 2 + 1);
        }

        if ($current >= $last - $side) {
            $start = max(1, $last - $side * 2);
        }

        $linkClass = 'tw-inline-flex tw-h-9 tw-min-w-9 tw-items-center tw-justify-center tw-rounded-card tw-px-2 tw-text-sm tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600';
    @endphp

    <nav {{ $attributes->merge(['class' => 'ds-pagination tw-flex tw-items-center tw-gap-1']) }} aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="{{ $linkClass }} tw-cursor-not-allowed tw-text-neutral-400 dark:tw-text-neutral-600" aria-disabled="true">
                <x-lucide-chevron-left class="tw-h-4 tw-w-4" />
                <span class="tw-sr-only">Previous</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }} tw-text-neutral-600 hover:tw-bg-neutral-100 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">
                <x-lucide-chevron-left class="tw-h-4 tw-w-4" />
                <span class="tw-sr-only">Previous</span>
            </a>
        @endif

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" class="{{ $linkClass }} tw-text-neutral-600 hover:tw-bg-neutral-100 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">1</a>
            @if ($start > 2)
                <span class="tw-px-1 tw-text-sm tw-text-neutral-400">&hellip;</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page === $current)
                <span aria-current="page" class="{{ $linkClass }} tw-bg-primary-500 tw-text-ink-950">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $linkClass }} tw-text-neutral-600 hover:tw-bg-neutral-100 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="tw-px-1 tw-text-sm tw-text-neutral-400">&hellip;</span>
            @endif
            <a href="{{ $paginator->url($last) }}" class="{{ $linkClass }} tw-text-neutral-600 hover:tw-bg-neutral-100 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }} tw-text-neutral-600 hover:tw-bg-neutral-100 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">
                <x-lucide-chevron-right class="tw-h-4 tw-w-4" />
                <span class="tw-sr-only">Next</span>
            </a>
        @else
            <span class="{{ $linkClass }} tw-cursor-not-allowed tw-text-neutral-400 dark:tw-text-neutral-600" aria-disabled="true">
                <x-lucide-chevron-right class="tw-h-4 tw-w-4" />
                <span class="tw-sr-only">Next</span>
            </span>
        @endif
    </nav>
@endif
