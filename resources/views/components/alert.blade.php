@props([
    'tone' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'info' => 'tw-border-accent-200 tw-bg-accent-50 tw-text-accent-900 dark:tw-border-accent-800 dark:tw-bg-accent-900/30 dark:tw-text-accent-100',
        'success' => 'tw-border-success-200 tw-bg-success-50 tw-text-success-900 dark:tw-border-success-800 dark:tw-bg-success-900/30 dark:tw-text-success-100',
        'warning' => 'tw-border-warning-200 tw-bg-warning-50 tw-text-warning-900 dark:tw-border-warning-800 dark:tw-bg-warning-900/30 dark:tw-text-warning-100',
        'danger' => 'tw-border-danger-200 tw-bg-danger-50 tw-text-danger-900 dark:tw-border-danger-800 dark:tw-bg-danger-900/30 dark:tw-text-danger-100',
    ];
@endphp

<div x-data="{ visible: true }" x-show="visible" role="alert"
    {{ $attributes->merge(['class' => 'ds-alert tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-border tw-p-4 '.($tones[$tone] ?? $tones['info'])]) }}>
    <span class="tw-mt-0.5 tw-shrink-0" aria-hidden="true">
        {{ $icon ?? '' }}
    </span>

    <div class="tw-min-w-0 tw-flex-1 tw-text-sm">
        @if ($title)
            <p class="tw-font-bold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'tw-mt-1' : '' }}">{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button type="button" @click="visible = false" aria-label="Dismiss alert"
            class="tw-shrink-0 tw-rounded-pill tw-p-1 tw-opacity-70 tw-transition hover:tw-opacity-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-current">
            <x-lucide-x class="tw-h-4 tw-w-4" />
        </button>
    @endif
</div>
