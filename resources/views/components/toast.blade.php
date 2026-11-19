@props([
    'tone' => 'success',
    'title' => null,
    'timeout' => 5000,
])

@php
    $tones = [
        'success' => 'tw-border-success-600 tw-text-success-100',
        'info' => 'tw-border-accent-600 tw-text-accent-100',
        'warning' => 'tw-border-warning-500 tw-text-warning-100',
        'danger' => 'tw-border-danger-600 tw-text-danger-100',
    ];
@endphp

<div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ (int) $timeout }})" x-show="show"
    x-transition:enter="tw-transition tw-duration-200" x-transition:enter-start="tw-translate-y-2 tw-opacity-0"
    x-transition:leave="tw-transition tw-duration-150" x-transition:leave-end="tw-opacity-0"
    role="status" aria-live="polite"
    {{ $attributes->merge(['class' => 'ds-toast tw-fixed tw-bottom-5 tw-right-5 tw-z-50 tw-flex tw-max-w-sm tw-items-start tw-gap-3 tw-rounded-card tw-border-l-4 tw-bg-ink-900 tw-p-4 tw-shadow-card-hover '.($tones[$tone] ?? $tones['success'])]) }}>
    <span class="tw-mt-0.5 tw-shrink-0" aria-hidden="true">
        {{ $icon ?? '' }}
    </span>
    <div class="tw-flex-1 tw-text-sm">
        @if ($title)
            <p class="tw-font-bold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'tw-mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
    <button type="button" @click="show = false" aria-label="Dismiss notification"
        class="tw-shrink-0 tw-rounded-pill tw-p-1 tw-opacity-70 tw-transition hover:tw-opacity-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-current">
        <x-lucide-x class="tw-h-4 tw-w-4" />
    </button>
</div>
