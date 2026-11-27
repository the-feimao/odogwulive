@props([
    'ticket',
    'currency' => null,
])

@php
    $isFree = strtolower((string) ($ticket->type ?? '')) === 'free' || (float) ($ticket->price ?? 0) === 0.0;
    $priceLabel = $isFree
        ? __('Free')
        : trim((string) $currency).number_format((float) $ticket->price);
    $available = (int) ($ticket->available_qty ?? 0);
    $soldOut = $available <= 0;
    $checkoutUrl = Auth::guard('appuser')->check()
        ? url('checkout/'.$ticket->id)
        : url('user/login');
@endphp

<article class="tw-flex tw-gap-4 tw-border-b tw-border-neutral-100 tw-py-5 last:tw-border-0 last:tw-pb-0 dark:tw-border-neutral-800">
    <span class="tw-mt-1.5 tw-h-10 tw-w-1.5 tw-shrink-0 tw-rounded-pill {{ $soldOut ? 'tw-bg-neutral-300 dark:tw-bg-neutral-700' : ($isFree ? 'tw-bg-success-500' : 'tw-bg-primary-500') }}"
        aria-hidden="true"></span>

    <div class="tw-min-w-0 tw-flex-1">
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
            <div class="tw-min-w-0">
                <h3 class="tw-text-base tw-font-bold tw-leading-snug tw-text-ink-950 dark:tw-text-neutral-50">
                    {{ $ticket->name }}
                </h3>
                <p class="tw-mt-1 tw-flex tw-flex-wrap tw-items-center tw-gap-x-2 tw-gap-y-1 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                    <span class="tw-font-semibold tw-text-ink-700 dark:tw-text-neutral-200">{{ $isFree ? __('Free entry') : __('Paid ticket') }}</span>
                    <span aria-hidden="true">·</span>
                    <span class="tw-font-mono">{{ $ticket->ticket_number }}</span>
                </p>
            </div>

            <p class="tw-shrink-0 tw-text-right tw-text-2xl tw-font-extrabold tw-leading-none tw-tracking-tight tw-tabular-nums tw-text-ink-950 dark:tw-text-neutral-50">
                {{ $priceLabel }}
            </p>
        </div>

        <div class="tw-mt-3 tw-flex tw-flex-wrap tw-items-center tw-gap-x-4 tw-gap-y-2 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
            <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                <x-lucide-clock class="tw-h-3.5 tw-w-3.5" />
                {{ __('Sales close').' '.$ticket->end_time?->format('M j, g:i A') }}
            </span>

            @if ($soldOut)
                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-font-bold tw-text-danger-700 dark:tw-text-danger-300">
                    <x-lucide-x class="tw-h-3.5 tw-w-3.5" />
                    {{ __('Sold Out') }}
                </span>
            @else
                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-font-semibold tw-text-ink-700 dark:tw-text-neutral-200">
                    <x-lucide-ticket class="tw-h-3.5 tw-w-3.5" />
                    {{ $available }} {{ $available === 1 ? __('ticket left') : __('tickets left') }}
                </span>
            @endif
        </div>

        @unless ($soldOut)
            <a href="{{ $checkoutUrl }}"
                class="tw-mt-4 tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-ink-950 tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-50 tw-transition-colors hover:tw-bg-primary-600 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-bg-primary-500 dark:tw-text-ink-950 dark:hover:tw-bg-primary-400">
                {{ __('Choose this ticket') }}
                <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
            </a>
        @endunless
    </div>
</article>
