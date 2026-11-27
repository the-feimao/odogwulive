@props([
    'series' => [],
    'currency' => '',
    'title' => 'Revenue trend',
    'subtitle' => 'Paid orders over the last six months',
])

@php
    $points = collect($series);
    $max = max(1, (float) $points->max('revenue'));
    $total = (float) $points->sum('revenue');
@endphp

<x-card :padded="false">
    <x-slot:header>
        <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-3">
            <div>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $title }}</h2>
                <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">{{ $subtitle }}</p>
            </div>
            <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                {{ $currency }}{{ number_format($total, 2) }}
            </p>
        </div>
    </x-slot:header>

    <div class="tw-p-5">
        <div class="tw-flex tw-h-44 tw-items-end tw-gap-2 sm:tw-gap-4" role="img"
            aria-label="{{ __('Revenue for the last six months, from').' '.$currency.number_format($points->first()['revenue'] ?? 0, 2).' '.__('to').' '.$currency.number_format($points->last()['revenue'] ?? 0, 2) }}">
            @foreach ($points as $point)
                <div class="tw-group tw-flex tw-h-full tw-flex-1 tw-flex-col tw-items-center tw-gap-2">
                    <div class="tw-flex tw-w-full tw-flex-1 tw-items-end">
                        <div class="tw-w-full tw-rounded-t-stub tw-bg-gradient-to-t tw-from-primary-500 tw-to-accent-400 tw-transition tw-duration-200 group-hover:tw-opacity-80 dark:tw-from-primary-400 dark:tw-to-accent-300"
                            style="height: {{ max(3, (int) round(((float) $point['revenue'] / $max) * 100)) }}%"
                            title="{{ $point['label'] }}: {{ $currency }}{{ number_format($point['revenue'], 2) }}"></div>
                    </div>
                    <span class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ \Illuminate\Support\Str::before($point['label'], ' ') }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="tw-mt-4 tw-flex tw-items-center tw-gap-4 tw-border-t tw-border-neutral-100 tw-pt-3 tw-text-2xs tw-font-medium tw-text-neutral-500 dark:tw-border-neutral-800 dark:tw-text-neutral-400">
            <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                <span class="tw-h-2 tw-w-2 tw-rounded-pill tw-bg-primary-500"></span>
                {{ __('Paid revenue') }}
            </span>
            <span>{{ __('Commission payouts are tracked in the settlement report.') }}</span>
        </div>
    </div>
</x-card>
