@props([
    'name',
    'label' => null,
    'value' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? 'editor-'.str_replace(['[', ']'], '_', $name);
    $inputId = $id.'-input';
    $errorMessage = $error ?? ($errors->first($name) ?? null);
@endphp

<div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
            {{ $label }}@if ($required)<span class="tw-text-danger-600" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <input id="{{ $inputId }}" type="hidden" name="{{ $name }}" value="{{ e(old($name, $value)) }}">

    <trix-editor input="{{ $inputId }}" id="{{ $id }}"
        @if ($required) required aria-required="true" @endif
        @if ($errorMessage) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except(['id'])->merge(['class' => 'ds-rich-text trix-content']) }}></trix-editor>

    @if ($errorMessage)
        <p id="{{ $id }}-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $errorMessage }}</p>
    @elseif ($help)
        <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $help }}</p>
    @endif
</div>
