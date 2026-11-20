@props([
    'label',
    'tone' => 'primary',
    'hint' => null,
])

@php
    $tones = [
        'primary' => 'tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/15 dark:tw-text-primary-300',
        'accent' => 'tw-bg-accent-100 tw-text-accent-700 dark:tw-bg-accent-500/15 dark:tw-text-accent-300',
        'success' => 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/15 dark:tw-text-success-300',
        'warning' => 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/15 dark:tw-text-warning-300',
        'danger' => 'tw-bg-danger-100 tw-text-danger-700 dark:tw-bg-danger-500/15 dark:tw-text-danger-300',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-5 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900']) }}>
    <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
        <div class="tw-min-w-0">
            <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                {{ $label }}
            </p>
            <div class="tw-mt-2 tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ $slot }}
            </div>
            @if ($hint)
                <p class="tw-mt-1 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $hint }}</p>
            @endif
        </div>

        @if (isset($icon))
            <span class="tw-flex tw-h-11 tw-w-11 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub {{ $tones[$tone] ?? $tones['primary'] }}">
                {{ $icon }}
            </span>
        @endif
    </div>
</div>
