@props([
    'name',
    'label' => null,
    'value' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? 'field-'.str_replace(['[', ']'], '_', $name);
    $errorMessage = $error ?? ($errors->first($name) ?? null);
@endphp

<div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
            {{ $label }}@if ($required)<span class="tw-text-danger-600" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <select id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required aria-required="true" @endif
        @if ($errorMessage) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except(['id'])->merge(['class' => 'tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-bg-ink-900 dark:tw-text-neutral-50 '.($errorMessage ? 'tw-border-danger-600 focus:tw-border-danger-600' : 'tw-border-neutral-200 hover:tw-border-neutral-300 focus:tw-border-accent-600 dark:tw-border-neutral-700 dark:hover:tw-border-neutral-600')]) }}>
        {{ $slot }}
    </select>

    @if ($errorMessage)
        <p id="{{ $id }}-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $errorMessage }}</p>
    @elseif ($help)
        <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $help }}</p>
    @endif
</div>
