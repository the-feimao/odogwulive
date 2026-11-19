@props([
    'title' => null,
    'size' => 'md',
])

@php
    $modalId = $attributes->get('id') ?? 'ds-modal-'.Illuminate\Support\Str::random(6);

    $sizes = [
        'sm' => 'tw-max-w-md',
        'md' => 'tw-max-w-xl',
        'lg' => 'tw-max-w-3xl',
        'xl' => 'tw-max-w-5xl',
    ];
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="ds-modal">
    <span @click="open = true">
        @if (isset($trigger))
            {{ $trigger }}
        @else
            <x-button variant="secondary" size="sm">Open dialog</x-button>
        @endif
    </span>

    <div x-cloak x-show="open" @click.self="open = false"
        class="tw-fixed tw-inset-0 tw-z-50 tw-flex tw-items-center tw-justify-center tw-bg-ink-950/70 tw-p-4"
        x-transition:enter="tw-transition tw-duration-150" x-transition:enter-start="tw-opacity-0"
        x-transition:leave="tw-transition tw-duration-100" x-transition:leave-end="tw-opacity-0">
        <div role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title"
            {{ $attributes->except(['id'])->merge(['class' => 'tw-w-full '.($sizes[$size] ?? $sizes['md']).' tw-max-h-[90vh] tw-overflow-y-auto tw-rounded-card tw-bg-neutral-0 tw-shadow-card-hover dark:tw-bg-ink-900']) }}>
            <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-b tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
                <h2 id="{{ $modalId }}-title" class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                    {{ $title ?? 'Dialog' }}
                </h2>
                <button type="button" @click="open = false" aria-label="Close dialog"
                    class="tw-rounded-pill tw-p-1 tw-text-neutral-500 tw-transition hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                    <x-lucide-x class="tw-h-5 tw-w-5" />
                </button>
            </div>

            <div class="tw-p-5">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="tw-flex tw-justify-end tw-gap-2 tw-border-t tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
