@props([
    'tone' => 'neutral',
    'size' => 'sm',
])

@php
    $tones = [
        'neutral' => 'tw-bg-neutral-100 tw-text-neutral-700 dark:tw-bg-neutral-800 dark:tw-text-neutral-200',
        'primary' => 'tw-bg-primary-100 tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300',
        'accent' => 'tw-bg-accent-100 tw-text-accent-800 dark:tw-bg-accent-500/20 dark:tw-text-accent-200',
        'success' => 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200',
        'warning' => 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/20 dark:tw-text-warning-200',
        'danger' => 'tw-bg-danger-100 tw-text-danger-700 dark:tw-bg-danger-500/20 dark:tw-text-danger-200',
    ];

    $sizes = [
        'xs' => 'tw-px-2 tw-py-0.5 tw-text-2xs',
        'sm' => 'tw-px-2.5 tw-py-1 tw-text-xs',
        'md' => 'tw-px-3 tw-py-1.5 tw-text-sm',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'ds-badge tw-inline-flex tw-items-center tw-gap-1 tw-rounded-pill tw-font-semibold tw-leading-none '.($tones[$tone] ?? $tones['neutral']).' '.($sizes[$size] ?? $sizes['sm'])]) }}>
    {{ $slot }}
</span>
