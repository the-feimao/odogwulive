@props([
    'event' => null,
    'title' => null,
    'image' => null,
    'date' => null,
    'location' => null,
    'price' => null,
    'href' => '#',
    'badge' => null,
])

@php
    $title = $title ?? ($event->name ?? $event->title ?? 'Untitled event');
    $date = $date ?? ($event->formatted_date ?? $event->start_date ?? $event->date ?? null);
    $location = $location ?? ($event->venue ?? $event->location ?? null);
    $image = $image ?? ($event->image ?? $event->thumbnail ?? null);
@endphp

<article {{ $attributes->merge(['class' => 'ds-event-card tw-group tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card tw-transition-shadow hover:tw-shadow-card-hover dark:tw-border-neutral-800 dark:tw-bg-ink-900']) }}>
    <a href="{{ $href }}" class="tw-block tw-overflow-hidden tw-bg-neutral-100 dark:tw-bg-neutral-800" tabindex="-1" aria-hidden="true">
        <div class="tw-aspect-video">
            @if ($image)
                <img src="{{ $image }}" alt="" loading="lazy" width="640" height="360"
                    class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
            @else
                <div class="tw-flex tw-h-full tw-w-full tw-items-center tw-justify-center tw-text-neutral-400 dark:tw-text-neutral-600">
                    <x-lucide-ticket class="tw-h-10 tw-w-10" />
                </div>
            @endif
        </div>
    </a>

    <div class="tw-p-5">
        @if ($badge)
            <x-badge tone="primary" class="tw-mb-3">{{ $badge }}</x-badge>
        @endif

        <h3 class="tw-text-lg tw-font-bold tw-leading-snug tw-text-ink-900 dark:tw-text-neutral-50">
            <a href="{{ $href }}" class="tw-transition-colors hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">{{ $title }}</a>
        </h3>

        @if ($date || $location)
            <div class="tw-mt-3 tw-flex tw-flex-wrap tw-items-center tw-gap-x-4 tw-gap-y-1.5 tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-400">
                @if ($date)
                    <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                        <x-lucide-calendar-days class="tw-h-4 tw-w-4 tw-shrink-0" />
                        <span>{{ $date }}</span>
                    </span>
                @endif
                @if ($location)
                    <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                        <x-lucide-map-pin class="tw-h-4 tw-w-4 tw-shrink-0" />
                        <span>{{ $location }}</span>
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="ds-ticket-divider tw-mx-5"></div>

    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-p-5 tw-pt-4">
        <span class="tw-text-lg tw-font-bold tw-text-primary-700 dark:tw-text-primary-400">{{ $price ?? 'Free' }}</span>
        <a href="{{ $href }}"
            class="tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-semibold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
            View event
            <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
        </a>
    </div>
</article>
