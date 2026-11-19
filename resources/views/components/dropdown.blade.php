@props([
    'label' => 'Options',
    'align' => 'left',
])

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="ds-dropdown tw-relative tw-inline-block">
    <button type="button" @click="open = !open" x-bind:aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="true"
        {{ $attributes->except(['class'])->merge(['class' => 'tw-inline-flex tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-bg-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100 dark:hover:tw-bg-neutral-800']) }}>
        @if (isset($trigger))
            {{ $trigger }}
        @else
            <span>{{ $label }}</span>
        @endif
        <x-lucide-chevron-down class="tw-h-4 tw-w-4 tw-transition-transform" x-bind:class="open ? 'tw-rotate-180' : ''" />
    </button>

    <div x-cloak x-show="open" x-transition:enter="tw-transition tw-duration-100" x-transition:enter-start="tw-opacity-0"
        class="tw-absolute tw-z-40 tw-mt-2 tw-min-w-48 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-1 tw-shadow-card-hover dark:tw-border-neutral-700 dark:tw-bg-ink-900 {{ $align === 'right' ? 'tw-right-0' : 'tw-left-0' }}"
        role="menu">
        {{ $slot }}
    </div>
</div>
