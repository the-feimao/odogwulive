@props([
    'label' => __('Total Orders'),
    'total' => 0,
    'pending' => 0,
    'completed' => 0,
    'cancelled' => 0,
])

@php
    $total = max(0, (int) $total);
    $pending = max(0, (int) $pending);
    $completed = max(0, (int) $completed);
    $cancelled = max(0, (int) $cancelled);

    // Leave room for orders that are outside the three legacy status buckets.
    $other = max(0, $total - $pending - $completed - $cancelled);
    $denominator = max(1, $total);

    $percentage = fn (int $value): float => $total > 0 ? round(($value / $denominator) * 100, 2) : 0.0;
    $completedWidth = $percentage($completed);
    $pendingWidth = $percentage($pending);
    $cancelledWidth = $percentage($cancelled);
    $otherWidth = max(0, 100 - $completedWidth - $pendingWidth - $cancelledWidth);
    $completionRate = $total > 0 ? (int) round(($completed / $denominator) * 100) : 0;
@endphp

<div class="card-statistic-2 tw-relative tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-5 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900">
    <div class="tw-pointer-events-none tw-absolute -tw-right-10 -tw-top-14 tw-h-32 tw-w-32 tw-rounded-full tw-bg-primary-100/80 tw-blur-2xl dark:tw-bg-primary-500/10" aria-hidden="true"></div>

    <div class="tw-relative">
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
            <div class="tw-min-w-0">
                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ $label }}
                </p>
                <div class="tw-mt-2 tw-flex tw-items-baseline tw-gap-2 tw-text-4xl tw-font-extrabold tw-leading-none tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                    <span class="card-stats order-total">{{ $total }}</span>
                    <span class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-400 dark:tw-text-neutral-500">
                        {{ __('All time') }}
                    </span>
                </div>
            </div>

            <span class="tw-flex tw-h-11 tw-w-11 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/15 dark:tw-text-primary-300">
                <x-lucide-ticket class="tw-h-5 tw-w-5" />
            </span>
        </div>

        <div class="tw-mt-5">
            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3">
                <p class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ __('Order mix') }}
                </p>
                <p class="tw-text-2xs tw-font-bold tw-text-ink-700 dark:tw-text-neutral-200">
                    <span data-order-completion>{{ $completionRate }}%</span>
                    <span class="tw-font-medium tw-text-neutral-400 dark:tw-text-neutral-500">{{ __('completed') }}</span>
                </p>
            </div>

            <div class="tw-mt-2 tw-flex tw-h-2 tw-overflow-hidden tw-rounded-pill tw-bg-neutral-100 dark:tw-bg-neutral-800" role="img"
                aria-label="{{ __('Order status breakdown') }}: {{ $completed }} {{ __('completed') }}, {{ $pending }} {{ __('pending') }}, {{ $cancelled }} {{ __('cancelled') }}">
                <div data-order-bar-completed class="tw-h-full tw-bg-success-500 tw-transition-all tw-duration-300" style="width: {{ $completedWidth }}%"></div>
                <div data-order-bar-pending class="tw-h-full tw-bg-warning-500 tw-transition-all tw-duration-300" style="width: {{ $pendingWidth }}%"></div>
                <div data-order-bar-cancelled class="tw-h-full tw-bg-danger-500 tw-transition-all tw-duration-300" style="width: {{ $cancelledWidth }}%"></div>
                <div data-order-bar-other class="tw-h-full tw-bg-neutral-300 tw-transition-all tw-duration-300 dark:tw-bg-neutral-700" style="width: {{ $otherWidth }}%"></div>
            </div>
        </div>

        <div class="card-stats tw-mt-4 tw-grid tw-grid-cols-3 tw-gap-1 tw-rounded-card tw-bg-neutral-50 tw-p-3 dark:tw-bg-neutral-950/40">
            <div class="tw-flex tw-flex-col tw-items-center tw-gap-0.5 tw-text-center">
                <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-warning-500" aria-hidden="true"></span>
                <p class="card-stats order-pending tw-text-base tw-font-extrabold tw-leading-none tw-text-warning-600 dark:tw-text-warning-500">{{ $pending }}</p>
                <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Pending') }}</p>
            </div>
            <div class="tw-flex tw-flex-col tw-items-center tw-gap-0.5 tw-border-x tw-border-neutral-200 tw-text-center dark:tw-border-neutral-800">
                <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-success-500" aria-hidden="true"></span>
                <p class="card-stats order-complete tw-text-base tw-font-extrabold tw-leading-none tw-text-success-600 dark:tw-text-success-500">{{ $completed }}</p>
                <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Completed') }}</p>
            </div>
            <div class="tw-flex tw-flex-col tw-items-center tw-gap-0.5 tw-text-center">
                <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-danger-500" aria-hidden="true"></span>
                <p class="card-stats order-cancel tw-text-base tw-font-extrabold tw-leading-none tw-text-danger-600 dark:tw-text-danger-500">{{ $cancelled }}</p>
                <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Cancelled') }}</p>
            </div>
        </div>
    </div>
</div>
