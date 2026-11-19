@props([
    'links' => [],
    'mobile' => false,
])

@foreach ($links as $link)
    <a href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif
        {{ $attributes->merge(['class' => $mobile
            ? 'tw-block tw-rounded-stub tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-transition-colors '.($link['active'] ? 'tw-bg-primary-100 tw-text-ink-950 dark:tw-bg-primary-500/20 dark:tw-text-primary-300' : 'tw-text-neutral-600 hover:tw-bg-neutral-100 hover:tw-text-ink-900 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50')
            : 'tw-inline-flex tw-items-center tw-border-b-2 tw-px-1 tw-py-2 tw-text-sm tw-font-semibold tw-transition-colors '.($link['active'] ? 'tw-border-primary-500 tw-text-ink-950 dark:tw-text-neutral-50' : 'tw-border-transparent tw-text-neutral-600 hover:tw-border-neutral-300 hover:tw-text-ink-900 dark:tw-text-neutral-400 dark:hover:tw-text-neutral-100')]) }}>
        {{ $link['label'] }}
    </a>
@endforeach
