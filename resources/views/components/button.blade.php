@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'full' => false,
])

@php
    $variants = [
        'primary' => 'tw-bg-primary-500 tw-text-ink-950 hover:tw-bg-primary-400 focus-visible:tw-ring-primary-600',
        'secondary' => 'tw-bg-neutral-100 tw-text-ink-900 hover:tw-bg-neutral-200 dark:tw-bg-neutral-800 dark:tw-text-neutral-50 dark:hover:tw-bg-neutral-700',
        'ghost' => 'tw-bg-transparent tw-text-ink-800 hover:tw-bg-neutral-100 dark:tw-text-neutral-100 dark:hover:tw-bg-neutral-800',
        'danger' => 'tw-bg-danger-600 tw-text-white hover:tw-bg-danger-700',
    ];

    $sizes = [
        'sm' => 'tw-h-8 tw-px-3 tw-text-xs',
        'md' => 'tw-h-10 tw-px-4 tw-text-sm',
        'lg' => 'tw-h-12 tw-px-5 tw-text-base',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'ds-btn tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-font-semibold tw-transition-colors tw-duration-150 tw-shadow-card disabled:tw-cursor-not-allowed disabled:tw-opacity-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-offset-2 '.$variantClass.' '.$sizeClass.($full ? ' tw-w-full' : '')]) }}
    @if ($disabled) disabled aria-disabled="true" @endif>
    @if (isset($icon))
        <span class="tw-shrink-0" aria-hidden="true">{{ $icon }}</span>
    @endif
    {{ $slot }}
</button>
