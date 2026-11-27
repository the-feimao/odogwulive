@props([
    'items' => [],
    'columns' => 4,
])

@php
    $grid = [
        2 => 'sm:tw-grid-cols-2',
        3 => 'sm:tw-grid-cols-2 lg:tw-grid-cols-3',
        4 => 'sm:tw-grid-cols-2 xl:tw-grid-cols-4',
    ][$columns] ?? 'sm:tw-grid-cols-2 xl:tw-grid-cols-4';
@endphp

<div {{ $attributes->merge(['class' => 'tw-grid tw-gap-3 '.$grid]) }}>
    @foreach ($items as $item)
        <div class="tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50/70 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-neutral-950/40">
            <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                <p class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ $item['label'] }}
                </p>
                @if (! empty($item['badge']))
                    <x-badge :tone="$item['badgeTone'] ?? 'neutral'" size="xs">{{ $item['badge'] }}</x-badge>
                @endif
            </div>

            <p class="tw-mt-2 tw-text-xl tw-font-extrabold tw-tabular-nums tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ $item['value'] }}
            </p>

            @if (! empty($item['hint']))
                <p class="tw-mt-1 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item['hint'] }}</p>
            @endif
        </div>
    @endforeach
</div>
