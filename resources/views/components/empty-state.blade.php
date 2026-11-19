@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'ds-empty-state tw-flex tw-flex-col tw-items-center tw-justify-center tw-px-5 tw-py-13 tw-text-center']) }}>
    <div class="tw-mb-4 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-300">
        @if (isset($icon))
            {{ $icon }}
        @else
            <x-lucide-inbox class="tw-h-6 tw-w-6" />
        @endif
    </div>

    <h3 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $title }}</h3>

    @if ($description)
        <p class="tw-mt-1.5 tw-max-w-sm tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">{{ $description }}</p>
    @endif

    @if (isset($action))
        <div class="tw-mt-5">{{ $action }}</div>
    @endif
</div>
