@props([
    'title',
    'parent' => null,
    'url' => null,
])

<nav aria-label="Breadcrumb" class="tw-flex tw-flex-wrap tw-items-center tw-gap-1.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
    <a href="{{ Auth::user()->hasRole('admin') ? url('admin/home') : url('organization/home') }}"
        class="tw-font-semibold tw-transition hover:tw-text-accent-600 dark:hover:tw-text-accent-300">
        {{ __('Dashboard') }}
    </a>

    @if ($parent)
        <x-lucide-chevron-right class="tw-h-3 tw-w-3 tw-text-neutral-300 dark:tw-text-neutral-600" />
        <a href="{{ url($url) }}"
            class="tw-font-semibold tw-transition hover:tw-text-accent-600 dark:hover:tw-text-accent-300">
            {{ $parent }}
        </a>
    @endif

    <x-lucide-chevron-right class="tw-h-3 tw-w-3 tw-text-neutral-300 dark:tw-text-neutral-600" />
    <span aria-current="page" class="tw-font-bold tw-text-ink-900 dark:tw-text-neutral-100">{{ $title }}</span>
</nav>
